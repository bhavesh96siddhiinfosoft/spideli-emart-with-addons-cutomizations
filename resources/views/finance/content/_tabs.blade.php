{{-- Spideli Finance content: the tabs of the kinds of content. Needs $tab. --}}
<ul class="nav nav-tabs mb-3">
    @foreach (['indicators' => __('Economic indicators'), 'rates' => __('Exchange rates'), 'securities' => __('Securities'), 'ipos' => __('IPOs'),
               'collections' => __('Stock collections'), 'news' => __('News'), 'billers' => __('Billers')] as $k => $label)
        <li class="nav-item"><a class="nav-link {{ $tab === $k ? 'active' : '' }}" href="{{ route('finance.content', ['tab' => $k]) }}">{{ $label }}</a></li>
    @endforeach
</ul>
