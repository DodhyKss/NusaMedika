@php
    $flags = $leaf['flags'];
    $menuId = $menu['id'];
    $formId = $leaf['form_id'];
    $labelAksi = ['create' => 'Tambah', 'read' => 'Lihat', 'update' => 'Ubah', 'delete' => 'Hapus'];
@endphp
<tr class="hover:bg-blue-50/40 transition-colors align-top">
    <td class="px-3 py-2.5">
        <div class="flex flex-wrap gap-x-4 gap-y-1">
            @foreach (['create', 'read', 'update', 'delete'] as $a)
                <label class="flex items-center gap-1.5 cursor-pointer select-none">
                    <input type="checkbox" name="{{ $a }}_form_ids[]" value="{{ $formId }}"
                           data-menu="menu-{{ $menuId }}"
                           class="rounded border-slate-300 text-blue-600 focus:ring-blue-500/30 leaf leaf-{{ $a }}"
                           @checked($flags[$a])>
                    <span class="text-[11px] font-medium text-slate-600">{{ $labelAksi[$a] }}</span>
                </label>
            @endforeach
        </div>
    </td>
    <td class="px-3 py-2.5 font-semibold text-slate-800">{{ $leaf['nama'] }}</td>
    <td class="px-3 py-2.5 pl-8 text-slate-700">{{ $path }}</td>
    <td class="px-3 py-2.5 font-mono text-xs text-slate-500">{{ $ref }}</td>
</tr>