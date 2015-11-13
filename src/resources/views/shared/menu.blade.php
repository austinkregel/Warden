<div class="panel panel-default panel-flush">
    <div class="panel-heading">
        Edit some things in the DB
    </div>
    <div class="panel-body">
        <div class="spark-settings-tabs">
            <ul class="nav spark-settings-tabs-stacked" role="tablist">
                @foreach(config('kregel.warden.models') as $menuitem => $classname)
                        <!-- Settings Dropdown -->
                <!-- Authenticated Right Dropdown -->
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">
                        {{ ucwords($menuitem) }} <span class="caret"></span>
                    </a>
                    <ul class="dropdown-menu" role="menu">
                        <!-- Settings -->
                        <li class="dropdown-header">Manage {{ ucwords($menuitem) }}s</li>
                        <li>
                            <a href="{{ route('warden::new-model', $menuitem) }}">
                                <i class="fa fa-btn fa-fw fa-cog"></i>New {{ ucwords($menuitem) }}
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('warden::models', $menuitem) }}">
                                <i class="fa fa-btn fa-fw fa-cog"></i>List all {{ ucwords($menuitem) }}s
                            </a>
                        </li>
                    </ul>
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>