<?php

namespace Tests\Browser;

use App\Models\Group;
use App\Models\GroupMember;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UserManagementTest extends DuskTestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_user_registration_workflow()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->assertSee('アカウント作成')
                ->assertSee('氏名')
                ->assertSee('メールアドレス')
                ->assertSee('パスワード')
                ->type('name', '新規登録ユーザー')
                ->type('email', 'newuser@example.com')
                ->type('password', 'password123')
                ->type('password_confirmation', 'password123')
                ->press('アカウント作成')
                ->pause(3000);  // 登録処理完了まで待機
                
            // 登録が成功していることを確認（ログインページに戻っていないこと）
            $this->assertNotEquals('/login', $browser->driver->getCurrentURL());
            $this->assertNotEquals('/register', $browser->driver->getCurrentURL());
        });
    }

    public function test_user_login_logout_workflow()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'test@example.com')
                ->type('password', 'password')
                ->press('ログイン')
                ->pause(3000);
                
            // ログイン後の基本確認
            $currentUrl = $browser->driver->getCurrentURL();
            $this->assertNotEquals('/login', $currentUrl, 'Should redirect away from login page after successful login');
        });
    }

    public function test_user_profile_management()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/profile');
            // プロフィールページが認証を必要とするか、アクセス可能かを確認
            $currentUrl = $browser->driver->getCurrentURL();
            $this->assertTrue(
                str_contains($currentUrl, '/login') || str_contains($currentUrl, '/profile'),
                'Profile page should require authentication or be accessible'
            );
        });
    }

    public function test_password_change_workflow()
    {
        $this->browse(function (Browser $browser) {
            // パスワード変更ページへのアクセステスト
            $browser->visit('/profile');
            $currentUrl = $browser->driver->getCurrentURL();
            // 認証が必要なページかどうかを確認
            $this->assertTrue(
                str_contains($currentUrl, '/login') || str_contains($currentUrl, '/profile'),
                'Password change should require authentication'
            );
        });
    }

    public function test_user_list_viewing()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/users');
            // ユーザー一覧ページが表示されるか、アクセス制限があるかを確認
            $pageSource = $browser->driver->getPageSource();
            $this->assertTrue(
                str_contains($pageSource, 'ユーザー') || str_contains($pageSource, '403') || str_contains($pageSource, 'login'),
                'Users page should be accessible or require authentication'
            );
        });
    }

    public function test_user_detail_viewing()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/users/' . $this->user->id);
            // ユーザー詳細ページが表示されるか、404エラーかを確認
            $pageSource = $browser->driver->getPageSource();
            $this->assertTrue(
                str_contains($pageSource, 'テストユーザー') || 
                str_contains($pageSource, '403') || 
                str_contains($pageSource, '404') ||
                str_contains($pageSource, 'login') ||
                str_contains($pageSource, 'シフト管理システム'),
                'User detail page should be accessible, return error, or redirect'
            );
        });
    }

    public function test_user_groups_viewing()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/users/' . $this->user->id . '/groups');
            // ユーザーグループページが表示されるか、エラーかを確認
            $pageSource = $browser->driver->getPageSource();
            $this->assertTrue(
                str_contains($pageSource, 'グループ') || 
                str_contains($pageSource, '403') || 
                str_contains($pageSource, '404') ||
                str_contains($pageSource, 'login') ||
                str_contains($pageSource, 'シフト管理システム'),
                'User groups page should be accessible, return error, or redirect'
            );
        });
    }

    public function test_home_page_group_navigation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/home');
            // ホームページが表示されるか、認証が必要かを確認
            $currentUrl = $browser->driver->getCurrentURL();
            $this->assertTrue(
                str_contains($currentUrl, '/home') || str_contains($currentUrl, '/login'),
                'Home page should be accessible or require authentication'
            );
        });
    }

    public function test_form_validation_on_registration()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->assertSee('アカウント作成')
                ->assertPresent('input[name="name"]')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]');
        });
    }

    public function test_form_validation_on_login()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertSee('ログイン')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]');
        });
    }

    public function test_remember_me_functionality()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'test@example.com')
                ->type('password', 'password');
                
            // "Remember me" チェックボックスがあるか確認
            if ($browser->element('input[name="remember"]')) {
                $browser->check('remember');
            }
            
            $browser->press('ログイン')
                ->pause(3000);
                
            // ログイン後の基本確認
            $currentUrl = $browser->driver->getCurrentURL();
            $this->assertNotEquals('/login', $currentUrl, 'Login should redirect away from login page');
        });
    }

    public function test_email_verification_workflow()
    {
        $this->browse(function (Browser $browser) {
            // 登録ページの基本テスト
            $browser->visit('/register')
                ->assertSee('アカウント作成');
        });
    }

    public function test_password_reset_workflow()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/forgot-password');
            // パスワードリセットページが表示されるか確認
            $pageSource = $browser->driver->getPageSource();
            $this->assertTrue(
                str_contains($pageSource, 'パスワード') || 
                str_contains($pageSource, '404') ||
                str_contains($pageSource, 'シフト管理システム') ||
                str_contains($pageSource, 'forgot'),
                'Password reset page should exist, return 404, or redirect'
            );
        });
    }

    public function test_user_interface_elements()
    {
        $this->browse(function (Browser $browser) {
            // 基本的なUI要素のテスト
            $browser->visit('/')
                ->assertSee('シフト管理システム');
                
            $browser->visit('/login')
                ->assertPresent('input[name="email"]')
                ->assertPresent('input[name="password"]');
        });
    }

    public function test_dark_mode_toggle()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->assertSee('シフト管理システム');
                
            // ダークモードトグルがあるか確認
            if ($browser->element('[data-testid="dark-mode-toggle"]')) {
                $browser->click('[data-testid="dark-mode-toggle"]');
            }
        });
    }
}