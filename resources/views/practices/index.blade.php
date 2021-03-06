@extends('shared.layouts.page')

@section('title', 'Практики')

@section('css')
    @parent
    <link rel="stylesheet" href="{{ URL::asset('css/tables.css') }}" type="text/css">
@endsection

@section('page_sized_class', 'big_page')

@section('page_title', 'Практики')

@section('sub_menu')
    @if($total_user->canCreatePractices())
        <a href="{{ route('practices_create') }}" class="button button_green button_size_small">Создать практику</a>
    @endif
    <a href="{{ route('practices_join') }}" class="button button_blue button_size_small">Присоединиться к практике</a>
@endsection

@section('content')
    @if(count($practices) == 0)
        <p>
            Вы не зарегистрированы ни на одну практику!
        </p>
    @else
        <div class="mobile-table">
            <table class="table_linked">
                <thead>
                <th>ID</th>
                <th colspan="2">Название</th>
                <th class="td_small">Начало практики</th>
                <th class="td_small">Окончание практики</th>
                <th class="td_small">Закрыта ли</th>
                </thead>
                <tbody>
                @foreach($practices as $practice)
                    <tr>
                        @php
                            $new_messages_in_practice = $total_user->getCountNewMessagesInPractice($practice);
                        @endphp
                        <td><a href="{{ route('practices_view', $practice->id) }}" class="td_content">{{ $practice->id }}</a></td>
                        <td
                            @if($new_messages_in_practice == 0)
                                colspan="2"
                            @endif
                        ><a href="{{ route('practices_view', $practice->id) }}" class="td_content">{{ $practice->name }}</a></td>
                        @if($new_messages_in_practice > 0)
                            <td class="td_small td_small_padding td_notification">
                                <a href="{{ route('practices_view', $practice->id) }}" class="td_content">Новых сообщений: {{ $new_messages_in_practice }}!</a>
                            </td>
                        @endif
                        <td class="td_small"><a href="{{ route('practices_view', $practice->id) }}" class="td_content">{{ $practice->start_at }}</a></td>
                        <td class="td_small"><a href="{{ route('practices_view', $practice->id) }}" class="td_content">{{ $practice->end_at }}</a></td>
                        <td class="td_small"><a href="{{ route('practices_view', $practice->id) }}" class="td_content">@if($practice->is_closed)Да@elseНет@endif</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        {{ $practices->links('shared.pagination') }}
    @endif
@endsection
