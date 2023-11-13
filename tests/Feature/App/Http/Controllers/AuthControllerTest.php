<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\SignInController;
use App\Http\Requests\SignInFormRequest;
use App\Http\Requests\SignUpFormRequest;
use App\Listeners\SendEmailNewUserListener;
use App\Notifications\NewUserNotification;
use Domain\Auth\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;


class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */

    public function it_login_page_success(): void
    {
        $this->get(action([SignInController::class, 'index']))
            ->assertOk()
            ->assertSee('Вход в аккаунт')
            ->assertViewIs('auth.index');
    }

    /**
     * @test
     * @return void
     *
     * */
    public function it_sign_up_page_success(): void
    {
        $this->get(action([SignInController::class, 'signUp']))
        ->assertOk()
        ->assertSee('Регистрация')
        ->assertViewIs('auth.sign-up');
    }

    /**
     * @test
     * @return void
     *
     * */
    public function it_forgot_page_success(): void
    {
        $this->get(action([SignInController::class, 'forgot']))
            ->assertOk()
            ->assertViewIs('auth.forgot-password');
    }


    /**
     * @test
     * @return void
     *
     * */
    public function it_sign_in_success(): void
    {
       $password = '12345678';

       $user = User::factory()->create([
           'email' => 'testing@mail.ru',
           'password' => bcrypt($password)
       ]);

       $request = SignInFormRequest::factory()->create([
           'email' => $user->email,
           'password' => $password
       ]);


       $response = $this->post(action([SignInController::class, 'signIn'], $request));

       $response
           ->assertValid()
           ->assertRedirect(route('home'));

       $this->assertAuthenticatedAs($user);
    }

    /**
     * @test
     * @return void
     *
     * */
    public function it_logout_success(): void
    {
        $user = User::factory()->create([
            'email' => 'testing@mail.ru',
        ]);

        $this->actingAs($user)->delete(action([SignInController::class, 'logOut']));

        $this->assertGuest();
    }



    /** @test */
    public function it_store_success(): void
    {


        Notification::fake();
        Event::fake();
        $request = SignUpFormRequest::factory()->create(
            [
                'email' => 'testing@cutcode.ru',
                'password' => '12345678',
                'password_confirmation' => '12345678'
            ]
        );

        $this->assertDatabaseMissing('users', [
           'email' => $request['email']
        ]);


        $response = $this->post(
            action([SignInController::class, 'store']),
            $request
        );

        $response->assertValid();

        $this->assertDatabaseHas('users', [
            'email' => $request['email']
        ]);

        $user = User::query()
            ->where('email', $request['email'])
            ->first();

        Event::assertDispatched(Registered::class);
        Event::assertListening(Registered::class, SendEmailNewUserListener::class);

        $event = new Registered($user);
        $listener = new SendEmailNewUserListener();
        $listener->handle($event);

        Notification::assertSentTo($user, NewUserNotification::class);

        $this->assertAuthenticatedAs($user);

        $response
            ->assertRedirect(route('home'));
    }
}
