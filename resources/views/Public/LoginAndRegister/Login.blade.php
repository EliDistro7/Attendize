@extends('Shared.Layouts.MasterWithoutMenus')

@section('title', trans("User.login"))

@section('content')
    {!! Form::open(['url' => route("login"), 'id' => 'login-form']) !!}
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <!-- Language Dropdown Switcher -->

          {{-- Language Switcher using Laravel Localization Package --}}
<div class="text-right" style="margin-bottom: 15px;">
    <form action="{{ route('language.switch') }}" method="POST" id="language-form" style="display: inline-block;">
        @csrf
        <div class="form-group" style="margin-bottom: 0;">
            <select name="language" class="form-control input-sm" onchange="window.location.href = this.options[this.selectedIndex].getAttribute('data-url')" style="width: auto; display: inline-block;">
                @foreach(LaravelLocalization::getSupportedLocales() as $localeCode => $properties)
                    <option 
                        value="{{ $localeCode }}" 
                        data-url="{{ LaravelLocalization::getLocalizedURL($localeCode, null, [], true) }}"
                        {{ LaravelLocalization::getCurrentLocale() === $localeCode ? 'selected' : '' }}>
                        {{ $properties['native'] }}
                    </option>
                @endforeach
            </select>
        </div>
    </form>
</div>

            <div class="panel">
                <div class="panel-body">
                    <div class="logo">
                        {!!Html::image('assets/images/logo-dark.png')!!}
                    </div>

                    @if(Session::has('failed'))
                        <h4 class="text-danger mt0">@lang("basic.whoops")! </h4>
                        <ul class="list-group">
                            <li class="list-group-item">@lang("User.login_fail_msg")</li>
                        </ul>
                    @endif

                    <div class="form-group">
                        {!! Form::label('email', trans("User.email"), ['class' => 'control-label']) !!}
                        {!! Form::text('email', null, ['class' => 'form-control', 'autofocus' => true]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('password', trans("User.password"), ['class' => 'control-label']) !!}
                        (<a class="forgotPassword" href="{{route('forgotPassword')}}" tabindex="-1">@lang("User.forgot_password?")</a>)
                        {!! Form::password('password',  ['class' => 'form-control']) !!}
                    </div>

                    @include('Public.LoginAndRegister.Partials.CaptchaSection')

                    <div class="form-group">
                        <p><input class="btn btn-block btn-success" type="submit" value="@lang('User.login')"></p>
                    </div>

                    @if(Utils::isAttendize())
                    <div class="signup">
                        <span>@lang("User.dont_have_account_button", ["url"=> route('showSignup')])</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    {!! Form::close() !!}
@stop