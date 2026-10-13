<?php

namespace Tests\Unit;

use App\Libraries\Auth;
use CodeIgniter\Test\CIUnitTestCase;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use ReflectionClass;
use stdClass;

/**
 * Génération et lecture des jetons JWT de l'API.
 *
 * @internal
 */
final class AuthJwtTest extends CIUnitTestCase
{
    private const KEY = 'cle-de-test-suffisamment-longue-pour-hs256';

    private function makeAuth(): Auth
    {
        $auth = (new ReflectionClass(Auth::class))->newInstanceWithoutConstructor();
        // un objet non nul court-circuite _requireDeps() (modèles + client REST)
        $auth->_set('users', new stdClass());
        $auth->_set('secretKey', self::KEY);

        $user           = new stdClass();
        $user->autorize = true;
        $user->type     = 'user';
        $user->name     = 'Alice';
        $user->id       = 7;
        $user->role_id  = 3;
        $user->msg      = '';
        $auth->_set('connected_user', $user);

        return $auth;
    }

    public function testEncodeJwtProducesSignedToken(): void
    {
        $auth = $this->makeAuth();
        $auth->EncodeJWT();

        $user    = $auth->_get('connected_user');
        $decoded = JWT::decode($user->token, new Key(self::KEY, 'HS256'));

        $this->assertSame('API access', $decoded->aud);
        $this->assertSame(7, $decoded->data->id);
        $this->assertSame('Alice', $decoded->data->name);
        $this->assertSame($decoded->exp, $user->expireAt);
        $this->assertGreaterThan(time(), $decoded->exp);
    }

    public function testTokenIsRejectedWithAnotherKey(): void
    {
        $auth = $this->makeAuth();
        $auth->EncodeJWT();

        $this->expectException(\Firebase\JWT\SignatureInvalidException::class);
        JWT::decode($auth->_get('connected_user')->token, new Key('une-autre-cle-de-test-tout-aussi-longue', 'HS256'));
    }

    public function testDecodeJwtRestoresIdentity(): void
    {
        $emitter = $this->makeAuth();
        $emitter->EncodeJWT();
        $token = $emitter->_get('connected_user')->token;

        $receiver = $this->makeAuth();
        $receiver->_set('connected_user', (function () {
            $u           = new stdClass();
            $u->autorize = false;
            $u->type     = 'none';
            $u->name     = 'nobody';
            $u->id       = 0;
            $u->role_id  = 0;
            $u->msg      = '';

            return $u;
        })());
        $receiver->DecodeJWT($token);

        $restored = $receiver->_get('connected_user');
        $this->assertTrue($restored->autorize);
        $this->assertSame('Alice', $restored->name);
        $this->assertSame(7, $restored->id);
        $this->assertSame(3, $restored->role_id);
    }

    public function testVerifyJwtReturnsUserDataOrNull(): void
    {
        $auth = $this->makeAuth();
        $auth->EncodeJWT();
        $jwt = $auth->_get('connected_user')->token;

        $data = $auth->VerifyJWT($jwt);
        $this->assertSame(3, $data->role_id);
        $this->assertTrue($data->autorize);

        $this->assertNull($auth->VerifyJWT('pas.un.jwt'));
        $this->assertNull($auth->VerifyJWT(''));
        $this->assertNull($auth->VerifyJWT(JWT::encode(['data' => ['autorize' => true]], 'autre-cle-suffisamment-longue-pour-hs256', 'HS256')));
    }
}
