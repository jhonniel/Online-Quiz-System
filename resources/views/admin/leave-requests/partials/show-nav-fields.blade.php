@foreach(array_filter(['from' => request('from'), 'return' => request('return')]) as $navKey => $navVal)
    <input type="hidden" name="{{ $navKey }}" value="{{ $navVal }}">
@endforeach
