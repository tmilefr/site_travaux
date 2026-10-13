<?php

namespace Tests\Unit;

use App\Libraries\Acl;
use CodeIgniter\Test\CIUnitTestCase;
use ReflectionClass;
use stdClass;

/**
 * Règles d'accès de la bibliothèque Acl (sans base de données).
 *
 * @internal
 */
final class AclTest extends CIUnitTestCase
{
    /** Instancie Acl sans son constructeur (qui lit routeur, session et modèles). */
    private function makeAcl(?stdClass $user, array $props = []): Acl
    {
        $acl = (new ReflectionClass(Acl::class))->newInstanceWithoutConstructor();
        $acl->_set('usercheck', $user);
        $acl->_set('controller', 'admwork_controller');
        $acl->_set('action', 'index');
        foreach ($props as $name => $value) {
            $acl->_set($name, $value);
        }

        return $acl;
    }

    private function user(bool $authorized = true, int $roleId = 3): stdClass
    {
        $u           = new stdClass();
        $u->autorize = $authorized;
        $u->type     = 'user';
        $u->name     = 'Alice';
        $u->id       = 7;
        $u->role_id  = $roleId;

        return $u;
    }

    public function testGuestIsNotLogged(): void
    {
        $acl = $this->makeAcl($this->user(false));

        $this->assertFalse($acl->IsLog());
        $this->assertFalse($acl->getType());
        $this->assertFalse($acl->GetUserName());
        $this->assertFalse($acl->getUserId());
        $this->assertFalse($acl->getUserRoleId());
    }

    public function testLoggedUserAccessors(): void
    {
        $acl = $this->makeAcl($this->user());

        $this->assertTrue($acl->IsLog());
        $this->assertSame('user', $acl->getType());
        $this->assertSame('Alice', $acl->GetUserName());
        $this->assertSame(7, $acl->getUserId());
        $this->assertSame(3, $acl->getUserRoleId());
    }

    public function testGuestHasNoAccessUnlessDontCheck(): void
    {
        $this->assertFalse($this->makeAcl($this->user(false))->hasAccess('admwork_controller/index'));
        $this->assertTrue($this->makeAcl($this->user(false), ['DontCheck' => true])->hasAccess('admwork_controller/index'));
    }

    public function testLoggedUserNeedsGrantedPermission(): void
    {
        $acl = $this->makeAcl($this->user(), ['permissions' => [3 => ['admwork_controller/index', 'home/index']]]);

        $this->assertTrue($acl->hasAccess(), 'permission déduite du contrôleur et de l\'action courants');
        $this->assertTrue($acl->hasAccess('home/index'));
        $this->assertTrue($acl->hasAccess('HOME/Index'), 'la comparaison est insensible à la casse');
        $this->assertFalse($acl->hasAccess('admwork_controller/delete'));
    }

    public function testRoleWithoutPermissionsIsDenied(): void
    {
        $acl = $this->makeAcl($this->user(true, 9), ['permissions' => [3 => ['home/index']]]);

        $this->assertFalse($acl->hasAccess('home/index'));
    }

    public function testGuestPagesContainLoginAndLogout(): void
    {
        $pages = $this->makeAcl(null)->getGuestPages();

        $this->assertContains('home/login', $pages);
        $this->assertContains('home/logout', $pages);
        $this->assertNotContains('admwork_controller/index', $pages);
    }
}
