{{-- filepath: d:/OSPanel/home/kovcheg/resources/views/components/user-photo.blade.php --}}
@props(['photo'])

<div class="user-photo">
    @if($photo)
        <img src="{{ asset('storage/' . $photo) }}" alt="User Photo" class="rounded-full w-12 h-12 object-cover">
    @else
        <div class="rounded-full w-12 h-12 bg-gray-300 flex items-center justify-center text-gray-500">
            <span class="text-sm">N/A</span>
        </div>
    @endif
</div>
