@extends(config('kregel.warden.views.base-layout'))

@section('errors')
    @include('warden::shared.errors')
@stop

@section('content')
    <style>
        .method-button {
            background: none;
            border: none;
            color: #2B5A84;
            padding: 0;
            margin: 0;
        }

        .method-button:hover,
        .method-button:active,
        .method-button:focus {
            color: #18334a;
            outline: none;
        }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/1.0.8/vue.js"></script>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                @include('warden::shared.menu')
            </div>
            <div class="col-md-8">
                <div class="panel panel-default ">
                    <div class="panel-heading">
                        <h3>All entries for {{ ucwords($model_name) }}</h3>
                    </div>
                    <!-- .box-header -->
                    <div class="panel-body" id="vue-form-wrapper">
                        <div id="response" v-show="response">@{{ response }}
                            <div class="close" @click="close">&times;</div>
                        </div>

                        <table class="table">
                            <thead>
                                @foreach($field_names as $field)
                                {!! ((stripos($field, 'password')=== false) ?'<th>'.e($field).'</th>': '') !!}
                                @endforeach
                                <th style="width:40px">Edit</th>
                                <th style="width:54px">Delete</th>
                            </thead>
                            <tbody>
                                @foreach($models as $model)
                                <tr>
                                    @foreach($field_names as $field)
                                    @if(stripos($field, 'password') === false)
                                        <td>
                                            @if(empty($model->$field))
                                                <i>No data here</i>
                                            @else
                                                {{$model->$field}}
                                            @endif
                                        </td>
                                    @endif
                                    @endforeach
                                    <td>
                                        <span style="font-size:24px;">
                                           <a href="{{url(config('kregel.warden.route').'/'.$model_name.'/manage/'.$model->id)}}">
                                            <i class="@if(config('kregel.warden.using.fontawesome') === true) fa fa-edit @else glyphicon glyphicon-edit @endif"></i>
                                        </a>
                                        </span>
                                    </td>
                                    <td>
                                        <span style="text-align:right;float:right; font-size:24px;padding-right:10px;">
                                            <form action="{{ route('warden::api.delete-model', [$model_name, $model->id]) }}"
                                                  method='post' @submit.prevent="makeRequest">


                                                <button type="submit" class="method-button">
                                                <i class="@if(config('kregel.warden.using.fontawesome') === true) fa fa-trash-o @else glyphicon glyphicon-trash @endif"></i>
                                                </button>
                                                {{--<input type="submit" value="Text">--}}
                                            </form>
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                                </tbody>
                        </table>


                        <div class="text-center">
                            {!! $models->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        var vm, form;
        vm = new Vue({
            el: "#vue-form-wrapper",
            data: {
                _token: '{{csrf_token()}}',
                _method: 'DELETE',
                response: ''
            },
            methods: {
                makeRequest: function (e) {
                    request(e.target.action, this.$data, function (responseArea) {
                        if (responseArea.classList.contains('alert')) {
                            responseArea.className += 'alert-success ';
                            responseArea.className = responseArea.className.replace(/\balert-.*\s/g, ' alert-success');
                            $(e.target).parent().parent().parent().remove();
                        }
                    }, function (responseArea) {
                        if (responseArea.classList.contains('alert')) {
                            responseArea.className += 'alert-warning ';
                            responseArea.className = responseArea.className.replace(/\balert-.*\s/g, ' alert-warning');
                        }
                    }, function (responseArea) {
                        if (responseArea.classList.contains('alert')) {
                            responseArea.className += 'alert-danger ';
                            responseArea.className = responseArea.className.replace(/\balert-.*\s/g, ' alert-danger');
                        }
                    });
                    form = e.target;
                },
                close: function (e) {
                    this.response = '';
                }

            },
        });

        @include('formmodel::request', ['type' => 'DELETE'])

    </script>

@stop
