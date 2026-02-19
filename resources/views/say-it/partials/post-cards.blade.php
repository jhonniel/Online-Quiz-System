@foreach($posts as $post)
    @include('say-it.partials.post-card', ['post' => $post, 'sessionCodename' => $sessionCodename ?? session('sayit_codename')])
@endforeach
