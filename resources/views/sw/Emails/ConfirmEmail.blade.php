@extends('sw.Emails.Layouts.Master')

@section('message_content')

<p>Hujambo {{$first_name}}</p>
<p>
    Asante kwa kujisajili kwa {{ config('attendize.app_name') }}. Tunafurahi kukupokea katika timu yetu.
</p>

<p>
    Unaweza kuunda tukio lako la kwanza na kuthibitisha barua pepe yako kwa kutumia kiungo hapa chini.
</p>

<div style="padding: 5px; border: 1px solid #ccc;">
   {{route('confirmEmail', ['confirmation_code' => $confirmation_code])}}
</div>
<br><br>
<p>
    Kama una maswali yoyote, maoni au mapendekezo huru kujibu barua pepe hii.
</p>
<p>
    Asante
</p>

@stop

@section('footer')


@stop