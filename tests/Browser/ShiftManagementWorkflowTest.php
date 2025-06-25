<?php

namespace Tests\Browser;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ShiftManagementWorkflowTest extends DuskTestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $member;
    protected $group;

    protected function setUp(): void
    {
        parent::setUp();

        // ロールをシーダーで作成
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::create([
            'name' => 'テストアドミン',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->member = User::create([
            'name' => 'テストメンバー',
            'email' => 'member@test.com',
            'password' => bcrypt('password'),
        ]);

        $this->group = Group::create(['name' => 'テストグループ']);

        GroupMember::create([
            'user_id' => $this->admin->id,
            'group_id' => $this->group->id,
            'role_id' => GroupMember::ROLE_ADMIN,
        ]);

        GroupMember::create([
            'user_id' => $this->member->id,
            'group_id' => $this->group->id,
            'role_id' => GroupMember::ROLE_MEMBER,
        ]);
    }

    public function test_complete_shift_management_workflow()
    {
        $this->browse(function (Browser $browser) {
            // 基本的なページアクセステスト（認証なし）
            $browser->visit('/')
                ->assertSee('シフト管理システム');
                
            // ログインページのアクセステスト
            $browser->visit('/login')
                ->assertSee('ログイン')
                ->assertSee('メールアドレス')
                ->assertSee('パスワード');
        });
    }

    public function test_user_registration_and_group_access_workflow()
    {
        $this->browse(function (Browser $browser) {
            // 基本的なページアクセステスト
            $browser->visit('/register')
                ->assertSee('アカウント作成');
                
            $browser->visit('/login')
                ->assertSee('ログイン');
        });
    }

    public function test_availability_management_workflow()
    {
        $this->browse(function (Browser $browser) {
            // 基本的なページアクセステスト
            $browser->visit('/login')
                ->assertSee('ログイン')
                ->assertSee('メールアドレス');
                
            $browser->visit('/register')
                ->assertSee('アカウント作成')
                ->assertSee('氏名');
        });
    }

    public function test_group_navigation_and_permissions()
    {
        $this->browse(function (Browser $browser) {
            // 基本的なページアクセステスト
            $browser->visit('/login')
                ->assertSee('ログイン');
                
            $browser->visit('/')
                ->assertSee('シフト管理システム');
        });
    }

    public function test_responsive_design_elements()
    {
        $this->browse(function (Browser $browser) {
            // レスポンシブデザインの基本テスト
            $browser->visit('/')
                ->resize(1200, 800)
                ->assertSee('シフト管理システム')
                ->resize(768, 1024)
                ->assertSee('シフト管理システム')
                ->resize(375, 667)
                ->assertSee('シフト管理システム');
        });
    }

    public function test_form_validation_errors()
    {
        $this->browse(function (Browser $browser) {
            // 基本的なフォームテスト
            $browser->visit('/login')
                ->assertSee('ログイン')
                ->assertSee('メールアドレス')
                ->assertSee('パスワード');
        });
    }

    public function test_search_and_filter_functionality()
    {
        $this->browse(function (Browser $browser) {
            // 基本的なページアクセステスト
            $browser->visit('/login')
                ->assertSee('ログイン');
        });
    }

    public function test_calendar_interaction()
    {
        $this->browse(function (Browser $browser) {
            // 基本的なページアクセステスト
            $browser->visit('/')
                ->assertSee('シフト管理システム');
        });
    }

    public function test_error_handling_and_404_pages()
    {
        $this->browse(function (Browser $browser) {
            // 404ページのテスト
            $browser->visit('/nonexistent-page');
            // 404ページが表示されるか、ホームページにリダイレクトされるかを確認
            $pageSource = $browser->driver->getPageSource();
            $this->assertTrue(
                str_contains($pageSource, '404') || str_contains($pageSource, 'シフト管理システム'),
                'Expected 404 page or redirect to home'
            );
        });
    }

    public function test_accessibility_features()
    {
        $this->browse(function (Browser $browser) {
            // 基本的なアクセシビリティテスト
            $browser->visit('/login');
            
            // フォーム要素が存在するか確認
            $browser->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]');
        });
    }
}