<!-- resources/views/Shared/Partials/LanguageSwitcher.blade.php -->
<div class="language-switcher">
    <form action="{{ route('language.switch') }}" method="POST" id="language-form">
        @csrf
        <select name="language" class="form-control" onchange="this.form.submit()">
            @foreach(config('app.available_locales') as $locale => $language)
                <option value="{{ $locale }}" {{ app()->getLocale() == $locale ? 'selected' : '' }}>
                    {{ $language }}
                </option>
            @endforeach
        </select>
    </form>
</div>