{{-- filepath: d:/OSPanel/home/kovcheg/resources/views/components/user-photo.blade.php --}}
@props(['profile_photo_path' => null])

{{-- @dd($profile_photo_path); --}}
<div class="user-photo">
    @if($profile_photo_path)
        <img src="{{ asset('storage/' . $profile_photo_path) }}" alt="User Photo" class="rounded-full w-8 h-8 object-cover">@else
        <div class="rounded-full w-12 h-12 bg-gray-300 flex items-center justify-center text-gray-500">
            <span class="text-sm">N/A</span>
        </div>
    @endif
</div>
