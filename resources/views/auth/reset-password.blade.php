@section('title', 'Восстановление пароля')
@extends('layouts/auth')

@section('content')

   <x-forms.auth-forms title="Восстановление пароля"
           action="{{ route('password-reset.handle') }}"
           method="POST">
       @csrf
       <input type="hidden" name="token" value="{{ $token }}">
       <x-forms.text-input
           name="email"
           :isError="$errors->has('email')"
           type="email"
           placeholder="E-mail"
           value="{{ request('email') }}"
           required="true"
       />
       @csrf
       @error('email')
       <x-forms.error>
           {{$message}}
       </x-forms.error>
       @enderror
       <x-forms.text-input
           name="password"
           :isError="$errors->has('password')"
           type="password"
           placeholder="Пароль"
           required="true"
       />
       @error('password')
       <x-forms.error>
           {{$message}}
       </x-forms.error>
       @enderror
       <x-forms.text-input
           name="password_confirmation"
           :isError="$errors->has('password_confirmation')"
           type="password"
           placeholder="Потвердите пароль"
           required="true"
       />

       @error('password_confirmation')
       <x-forms.error>
           {{$message}}
       </x-forms.error>
       @enderror
       <x-forms.primary-button>
           Обновить пароль
       </x-forms.primary-button>
       <x-slot:socialAuth></x-slot:socialAuth>
       <x-slot:buttons></x-slot:buttons>
   </x-forms.auth-forms>
@endsection
