@extends('layouts.customer')

@section('container_class', 'max-w-none w-full')
@section('main_class', 'p-0 !mt-0')

@section('content')
<div class="flex flex-col h-screen bg-white overflow-hidden">
    <!-- Header Tabs -->
    <div class="flex border-b border-kv-border bg-gray-50">
        <a href="{{ route('customer.tables') }}" class="flex-1 py-3 text-center border-b-2 border-kv-blue text-kv-blue font-bold text-[14px] flex items-center justify-center gap-2 bg-white">
            <i data-feather="grid" class="w-4 h-4"></i> {{ __('ui.customer.table_map') }}
        </a>
        <a href="#" class="flex-1 py-3 text-center border-b-2 border-transparent text-gray-500 font-medium text-[14px] flex items-center justify-center gap-2 hover:bg-gray-100 transition">
            <i data-feather="file-text" class="w-4 h-4"></i> {{ __('ui.customer.orders') }}
        </a>
    </div>

    <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar: Rooms -->
        <div class="w-1/4 md:w-80 border-r border-kv-border overflow-y-auto bg-white flex flex-col">
            @foreach($rooms as $index => $room)
                <button onclick="showRoom('room-{{ $room->id }}', this)" 
                    class="room-tab w-full px-4 py-4 border-b border-gray-100 flex flex-col items-start gap-1 transition-colors {{ $index === 0 ? 'bg-blue-50 border-r-4 border-r-kv-blue' : 'hover:bg-gray-50' }}">
                    <div class="flex items-center gap-2">
                        <i data-feather="map-pin" class="w-4 h-4 text-gray-400"></i>
                        <span class="text-[13px] font-bold {{ $index === 0 ? 'text-kv-blue' : 'text-gray-700' }}">{{ $room->name }}</span>
                    </div>
                    <span class="text-[11px] text-gray-400">
                        {{ __('ui.customer.tables_count', ['count' => $room->tables->count()]) }}
                    </span>
                </button>
            @endforeach
        </div>

        <!-- Main: Tables Grid -->
        <div class="flex-1 bg-[#f0f2f5] p-6 pb-24 md:pb-32 overflow-y-auto relative">
            @foreach($rooms as $index => $room)
                <div id="room-{{ $room->id }}" class="room-content {{ $index === 0 ? '' : 'hidden' }}">
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-8 md:gap-10">
                        @foreach($room->tables as $table)
                            @php
                                $bgColor = 'bg-white';
                                $borderColor = 'border-gray-200';
                                $textColor = 'text-gray-800';
                                if($table->status === 'occupied') {
                                    $bgColor = 'bg-kv-blue';
                                    $borderColor = 'border-kv-blue';
                                    $textColor = 'text-white';
                                } elseif($table->status === 'reserved') {
                                    $bgColor = 'bg-kv-green';
                                    $borderColor = 'border-kv-green';
                                    $textColor = 'text-white';
                                }
                            @endphp
                            <a href="{{ route('customer.order.table', ['table_id' => $table->id]) }}" 
                                class="aspect-square md:aspect-auto md:h-48 {{ $bgColor }} border-2 {{ $borderColor }} rounded-xl flex items-center justify-center relative hover:shadow-2xl transition transform hover:-translate-y-2 group shadow-sm">
                                <span class="text-[20px] md:text-[32px] font-black {{ $textColor }}">{{ $table->name }}</span>
                                
                                <!-- Decorative chairs (scaled for PC) -->
                                <div class="absolute -top-1.5 left-1/2 -translate-x-1/2 w-6 md:w-8 h-2 md:h-3 bg-gray-300/80 rounded-t-full"></div>
                                <div class="absolute -bottom-1.5 left-1/2 -translate-x-1/2 w-6 md:w-8 h-2 md:h-3 bg-gray-300/80 rounded-b-full"></div>
                                <div class="absolute top-1/2 -left-1.5 -translate-y-1/2 w-2 md:w-3 h-6 md:h-8 bg-gray-300/80 rounded-l-full"></div>
                                <div class="absolute top-1/2 -right-1.5 -translate-y-1/2 w-2 md:w-3 h-6 md:h-8 bg-gray-300/80 rounded-l-full"></div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <!-- Legend -->
            <div class="fixed md:absolute bottom-0 md:bottom-8 left-0 md:left-1/2 md:-translate-x-1/2 w-full md:w-auto flex items-center justify-around md:justify-center md:gap-10 bg-white md:bg-white/95 backdrop-blur-sm px-4 md:px-10 py-3.5 md:rounded-full shadow-[0_-4px_20px_-5px_rgba(0,0,0,0.1)] md:shadow-xl text-[11px] md:text-[14px] font-bold border-t md:border border-gray-200/60 z-20 transition-all flex-nowrap overflow-x-auto no-scrollbar">
                <div class="flex items-center gap-2.5 whitespace-nowrap">
                    <div class="w-4 h-4 rounded-md bg-kv-green shadow-sm"></div>
                    <span class="text-gray-700">{{ __('ui.customer.booked') }}</span>
                </div>
                <div class="flex items-center gap-2.5 whitespace-nowrap">
                    <div class="w-4 h-4 rounded-md bg-white border-2 border-gray-200"></div>
                    <span class="text-gray-700">{{ __('ui.customer.available') }}</span>
                </div>
                <div class="flex items-center gap-2.5 whitespace-nowrap">
                    <div class="w-4 h-4 rounded-md bg-kv-blue shadow-sm"></div>
                    <span class="text-gray-700">{{ __('ui.customer.serving') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function showRoom(roomId, btn) {
        document.querySelectorAll('.room-content').forEach(el => el.classList.add('hidden'));
        document.getElementById(roomId).classList.remove('hidden');

        document.querySelectorAll('.room-tab').forEach(el => {
            el.classList.remove('bg-blue-50', 'border-r-4', 'border-r-kv-blue');
            el.querySelector('span').classList.remove('text-kv-blue');
            el.querySelector('span').classList.add('text-gray-700');
        });

        btn.classList.add('bg-blue-50', 'border-r-4', 'border-r-kv-blue');
        btn.querySelector('span').classList.remove('text-gray-700');
        btn.querySelector('span').classList.add('text-kv-blue');
    }
</script>
@endsection
