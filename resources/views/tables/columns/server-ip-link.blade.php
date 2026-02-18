<div class="flex items-center">
    <div class="server-name text-sm text-white rounded-full w-6 h-6 flex items-center justify-center" style="background-color: {{ $getRecord()->server->color ?? "#111111" }}">
        {{ $getRecord()->server?->name }}
    </div>
    <div class="server-ip" style="margin-left: 0.35rem; font-size: 0.85rem;">
        {{ $getRecord()->lowest_ip }}
        @if($getRecord()->lowest_ip != $getRecord()->highest_ip)
            - {{ $getRecord()->highest_ip }}
        @endif
    </div>
</div>
