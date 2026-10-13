<?php

namespace Tests\Unit;

use App\Libraries\PasswordAuthenticator;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Config\Travaux;

/**
 * Vérification et migration transparente des mots de passe (bcrypt / crypt / MD5).
 *
 * @internal
 */
final class PasswordAuthenticatorTest extends CIUnitTestCase
{
    private const SALT = '$2y$05$unSelDeTestPourCryptXX';

    private BaseConnection $conn;
    private PasswordAuthenticator $auth;

    protected function setUp(): void
    {
        parent::setUp();

        $this->conn = Database::connect('tests', false);
        $table = $this->conn->prefixTable('users');
        $this->conn->query("DROP TABLE IF EXISTS {$table}");
        $this->conn->query("CREATE TABLE {$table} (id INTEGER PRIMARY KEY, login TEXT, password TEXT)");

        $travaux               = config(Travaux::class);
        $travaux->passwordSalt = self::SALT;

        $this->auth = new PasswordAuthenticator($this->conn);
    }

    private function insertUser(string $login, string $storedHash): int
    {
        $this->conn->table('users')->insert(['login' => $login, 'password' => $storedHash]);

        return (int) $this->conn->insertID();
    }

    private function storedHash(int $id): string
    {
        return (string) $this->conn->table('users')->where('id', $id)->get()->getRow()->password;
    }

    public function testHashProducesVerifiableBcrypt(): void
    {
        $hash = $this->auth->hash('secret');

        $this->assertSame(60, strlen($hash));
        $this->assertStringStartsWith('$2y$', $hash);
        $this->assertTrue(password_verify('secret', $hash));
    }

    public function testVerifyAcceptsValidBcrypt(): void
    {
        $id  = $this->insertUser('alice', $this->auth->hash('secret'));
        $row = $this->auth->verify('users', 'login', 'alice', 'secret');

        $this->assertIsArray($row);
        $this->assertSame($id, (int) $row['id']);
    }

    public function testVerifyRejectsWrongPasswordAndUnknownLogin(): void
    {
        $this->insertUser('alice', $this->auth->hash('secret'));

        $this->assertFalse($this->auth->verify('users', 'login', 'alice', 'mauvais'));
        $this->assertFalse($this->auth->verify('users', 'login', 'inconnu', 'secret'));
    }

    public function testVerifyRejectsEmptyCredentials(): void
    {
        $this->insertUser('alice', $this->auth->hash('secret'));

        $this->assertFalse($this->auth->verify('users', 'login', '', 'secret'));
        $this->assertFalse($this->auth->verify('users', 'login', 'alice', ''));
        $this->assertFalse($this->auth->verify('users', 'login', null, null));
    }

    public function testVerifyRejectsEmptyStoredPassword(): void
    {
        $this->insertUser('vide', '');

        $this->assertFalse($this->auth->verify('users', 'login', 'vide', 'secret'));
    }

    public function testLegacyCryptHashIsMigratedToBcrypt(): void
    {
        $legacy = crypt('secret', self::SALT);
        $id     = $this->insertUser('bob', $legacy);

        $row = $this->auth->verify('users', 'login', 'bob', 'secret');

        $this->assertIsArray($row);
        $this->assertStringStartsWith('$2y$', $row['password']);
        $this->assertNotSame($legacy, $this->storedHash($id));
        $this->assertTrue(password_verify('secret', $this->storedHash($id)));
    }

    public function testLegacyMd5IsRefusedUnlessAllowed(): void
    {
        $id = $this->insertUser('famille', md5('secret'));

        $this->assertFalse($this->auth->verify('users', 'login', 'famille', 'secret'));
        $this->assertSame(md5('secret'), $this->storedHash($id), 'la base ne doit pas être modifiée en cas de refus');

        $row = $this->auth->verify('users', 'login', 'famille', 'secret', true);
        $this->assertIsArray($row);
        $this->assertTrue(password_verify('secret', $this->storedHash($id)));
    }

    public function testLegacyMd5WithWrongPasswordIsRefused(): void
    {
        $this->insertUser('famille', md5('secret'));

        $this->assertFalse($this->auth->verify('users', 'login', 'famille', 'faux', true));
    }

    public function testBcryptWithLowCostIsRehashed(): void
    {
        $weak = password_hash('secret', PASSWORD_BCRYPT, ['cost' => 4]);
        $id   = $this->insertUser('carol', $weak);

        $this->assertTrue($this->auth->needsRehash($weak));
        $this->assertIsArray($this->auth->verify('users', 'login', 'carol', 'secret'));
        $this->assertNotSame($weak, $this->storedHash($id));
        $this->assertFalse($this->auth->needsRehash($this->storedHash($id)));
    }

    public function testNeedsRehashFlagsNonBcryptHashes(): void
    {
        $this->assertTrue($this->auth->needsRehash(md5('x')));
        $this->assertFalse($this->auth->needsRehash($this->auth->hash('x')));
    }

    public function testDebugCollectsQueries(): void
    {
        $this->insertUser('alice', $this->auth->hash('secret'));
        $this->auth->verify('users', 'login', 'alice', 'secret');

        $this->assertNotEmpty($this->auth->getDebug());
    }
}
