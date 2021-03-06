@extends('shared.layouts.page')

@section('title', 'Администрирование')

@section('page_sized_class', 'big_page')

@section('page_title', 'Администрирование')

@section('sub_menu')
    <a href="{{ route('administration_roles') }}" class="button button_red button_size_small">Специальные роли пользователей</a>
    <a href="{{ route('administration_institutions') }}" class="button button_red button_size_small">Предприятия / Учебные заведения</a>
    <a href="{{ route('administration_bans') }}" class="button button_red button_size_small">Список всех банов в системе</a>
@endsection
