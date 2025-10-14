@extends('Shared.Layouts.MasterWithoutMenus')

@section('title')
    @lang("User.sign_up")
@stop

@section('head')
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Inter', system-ui, sans-serif;
        }
    </style>
@stop

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 flex items-center justify-center p-4">
        <div class="w-full max-w-2xl">
            {!! Form::open(['url' => route("showSignup"), 'id' => 'signup-form']) !!}
            
            <div class="bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden">
                <!-- Header Section -->
                <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-8 py-10 text-center">
                    <div class="mb-4 flex justify-center">
                        {!! Html::image('assets/images/logo-dark.png', 'Logo', ['class' => 'h-12 brightness-0 invert']) !!}
                    </div>
                    <h1 class="text-4xl font-black text-white tracking-tight">
                        @lang("User.sign_up")
                    </h1>
                    <p class="text-indigo-100 mt-2 text-lg font-medium">Create your account and get started</p>
                </div>

                <div class="px-8 py-10">
                    @if(Request::input('first_run'))
                        <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 rounded-r-lg p-4">
                            <div class="flex items-start">
                                <svg class="w-6 h-6 text-blue-500 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                                <p class="text-blue-800 font-semibold">@lang("User.sign_up_first_run")</p>
                            </div>
                        </div>
                    @endif

                    <!-- Name Fields -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            {!! Form::label('first_name', trans("User.first_name"), ['class' => 'block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide']) !!}
                            {!! Form::text('first_name', null, [
                                'class' => 'w-full px-4 py-3 rounded-lg border-2 transition-all duration-200 font-medium ' . 
                                ($errors->has('first_name') 
                                    ? 'border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100' 
                                    : 'border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100'),
                                'placeholder' => 'John'
                            ]) !!}
                            @if($errors->has('first_name'))
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $errors->first('first_name') }}</p>
                            @endif
                        </div>
                        <div>
                            {!! Form::label('last_name', trans("User.last_name"), ['class' => 'block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide']) !!}
                            {!! Form::text('last_name', null, [
                                'class' => 'w-full px-4 py-3 rounded-lg border-2 transition-all duration-200 font-medium ' . 
                                ($errors->has('last_name') 
                                    ? 'border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100' 
                                    : 'border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100'),
                                'placeholder' => 'Doe'
                            ]) !!}
                            @if($errors->has('last_name'))
                                <p class="mt-2 text-sm font-semibold text-red-600">{{ $errors->first('last_name') }}</p>
                            @endif
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div class="mb-6">
                        {!! Form::label('email', trans("User.email"), ['class' => 'block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide']) !!}
                        {!! Form::email('email', null, [
                            'class' => 'w-full px-4 py-3 rounded-lg border-2 transition-all duration-200 font-medium ' . 
                            ($errors->has('email') 
                                ? 'border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100' 
                                : 'border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100'),
                            'placeholder' => 'john.doe@example.com'
                        ]) !!}
                        @if($errors->has('email'))
                            <p class="mt-2 text-sm font-semibold text-red-600">{{ $errors->first('email') }}</p>
                        @endif
                    </div>

                    <!-- Password Fields -->
                    <div class="mb-6">
                        {!! Form::label('password', trans("User.password"), ['class' => 'block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide']) !!}
                        {!! Form::password('password', [
                            'class' => 'w-full px-4 py-3 rounded-lg border-2 transition-all duration-200 font-medium ' . 
                            ($errors->has('password') 
                                ? 'border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100' 
                                : 'border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100'),
                            'placeholder' => '••••••••'
                        ]) !!}
                        @if($errors->has('password'))
                            <p class="mt-2 text-sm font-semibold text-red-600">{{ $errors->first('password') }}</p>
                        @endif
                    </div>

                    <div class="mb-6">
                        {!! Form::label('password_confirmation', 'Password again', ['class' => 'block text-sm font-bold text-slate-700 mb-2 uppercase tracking-wide']) !!}
                        {!! Form::password('password_confirmation', [
                            'class' => 'w-full px-4 py-3 rounded-lg border-2 transition-all duration-200 font-medium ' . 
                            ($errors->has('password_confirmation') 
                                ? 'border-red-400 focus:border-red-500 focus:ring-4 focus:ring-red-100' 
                                : 'border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100'),
                            'placeholder' => '••••••••'
                        ]) !!}
                        @if($errors->has('password_confirmation'))
                            <p class="mt-2 text-sm font-semibold text-red-600">{{ $errors->first('password_confirmation') }}</p>
                        @endif
                    </div>

                    @if(Utils::isAttendizeCloud())
                    <div class="mb-6">
                        <label class="flex items-start cursor-pointer group">
                            <div class="relative flex items-center">
                                {!! Form::checkbox('terms_agreed', old('terms_agreed'), false, [
                                    'id' => 'terms_agreed',
                                    'class' => 'w-5 h-5 rounded border-2 border-slate-300 text-indigo-600 focus:ring-4 focus:ring-indigo-200 transition-all cursor-pointer'
                                ]) !!}
                            </div>
                            <span class="ml-3 text-sm font-medium text-slate-700 group-hover:text-slate-900">
                                {!! trans("User.terms_and_conditions", ["url"=>route('termsAndConditions')]) !!}
                            </span>
                        </label>
                        @if ($errors->has('terms_agreed'))
                            <p class="mt-2 text-sm font-semibold text-red-600">{{ $errors->first('terms_agreed') }}</p>
                        @endif
                    </div>
                    @endif

                    @include('Public.LoginAndRegister.Partials.CaptchaSection')

                    <!-- Submit Button -->
                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-600 to-blue-600 hover:from-indigo-700 hover:to-blue-700 text-white font-bold py-4 px-6 rounded-lg shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 text-lg tracking-wide uppercase">
                        @lang('User.sign_up')
                    </button>

                    <!-- Login Link -->
                    <div class="mt-6 text-center">
                        <p class="text-slate-600 font-medium">
                            {!! @trans("User.already_have_account", ["url"=>route("login")]) !!}
                        </p>
                    </div>
                </div>
            </div>
            
            {!! Form::close() !!}
        </div>
    </div>
@stop