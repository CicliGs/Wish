@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Виш-лист пользователя</h1>
    <table class="table">
        <thead>
            <tr>
                <th>Название</th>
                <th>Ссылка</th>
                <th>Картинка</th>
                <th>Цена</th>
                <th>Бронирование</th>
            </tr>
        </thead>
        <tbody>
            @foreach($wishes as $wish)
                <tr>
                    <td>{{ $wish->title }}</td>
                    <td>@if($wish->url)<a href="{{ $wish->url }}" target="_blank">Ссылка</a>@endif</td>
                    <td>@if($wish->image)<img src="{{ $wish->image }}" alt="image" style="max-width:60px;max-height:60px;">@endif</td>
                    <td>{{ $wish->price ? number_format($wish->price, 2) : '' }}</td>
                    <td>
                        @if(!$wish->is_reserved)
                            <form action="{{ route('wishes.reserve', $wish) }}" method="POST" style="display:inline-block">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm">Забронировать</button>
                            </form>
                        @else
                            <span class="text-success">Уже забронировано</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection 