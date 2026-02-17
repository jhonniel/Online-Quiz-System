@foreach($posts as $post)
    @include('say-it.partials.post-card', ['post' => $post])
@endforeach
