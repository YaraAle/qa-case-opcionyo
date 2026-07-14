<x-app-layout>

<div class="p-6">

<h1 class="text-2xl font-bold">
Agendar sesión
</h1>


@if(session('message'))

<div>
{{ session('message') }}
</div>

@endif



<form method="POST" action="/appointments">

@csrf


<label>
Especialista
</label>


<select name="specialist_id">

@foreach($specialists as $specialist)

<option value="{{ $specialist->id }}">

{{ $specialist->name }} -
{{ $specialist->specialty }}

</option>

@endforeach

</select>


<br><br>


<label>
Fecha y hora
</label>


<input
type="datetime-local"
name="scheduled_at"
required
>


<br><br>


<button type="submit">

Agendar

</button>


</form>


</div>

</x-app-layout>