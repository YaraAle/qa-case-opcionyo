<x-app-layout>

<div class="p-6">

<h1 class="text-2xl font-bold">
Suscripción
</h1>


@if(session('message'))

<p>
{{ session('message') }}
</p>

@endif



<form method="POST" action="/payment">

@csrf


<label>
Número tarjeta
</label>


<input 
    name="card"
    placeholder="4242424242424242"
>


<br><br>


<button type="submit">
Pagar
</button>


</form>


</div>

</x-app-layout>