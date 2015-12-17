@if (count($errors) > 0)
<div class="alert alert-danger">
  <strong>Whoops!</strong>
  There were some problems with your input.
  <ul>
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@elseif(Session::has('message'))
<div class="alert alert-success">
  <strong>It worked!</strong>
  <ul>
  @if(is_array(Session::get('message')))
    @foreach(Session::get('message') as $msg)
      <li>{{ $msg }}</li>
    @endforeach
  @else
    <li>{{Session::get('message')}}</li>
  @endif
  </ul>
</div>
@endif
