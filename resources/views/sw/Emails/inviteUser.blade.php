@extends('sw.Emails.Layouts.Master')

@section('message_content')

<p>Hujambo</p>
<p>
    Umeongezwa kwenye akaunti ya {{ config('attendize.app_name') }} na {{$inviter->first_name.' '.$inviter->last_name}}.
</p>

<p>
    Unaweza kuingia kwa kutumia maelezo yafuatayo.<br><br>
    
    Jina la mtumiaji: <b>{{$user->email}}</b> <br>
    Nenosiri: <b>{{$temp_password}}</b>
</p>

<p>
    Unaweza kubadilisha nenosiri lako la muda baada ya kuingia.
</p>

<div style="padding: 5px; border: 1px solid #ccc;" >
   {{route('login')}}
</div>
<br><br>
<p>
    Kama una maswali yoyote tafadhali jibu barua pepe hii.
</p>
<p>
    Asante
</p>

@stop

@section('footer')


@stop