
@php
    use App\Constants\Permissions;
@endphp

@section('content_header_title', $header_title)
@section('content_header_subtitle', $subtitle)
<div>
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Users</h3>
            <div class="card-tools">
                @can(Permissions::CREATE_USERS)
                    <a class="btn btn-success btn-sm" href="{{ route('sysadmin.users.create') }}">
                        <i class="fas fa-plus"></i> Add User
                    </a>
                @endcan
            </div>
        </div>
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Roles</th>
                        <th>Permissions (Direct & Role-based)</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                    <span class="badge badge-info">{{ $role->name }}</span>
                                @endforeach
                            </td>
                            <td>
                                {{-- <div>
                                    <strong>Direct Permissions:</strong>
                                    <ul>
                                        @foreach($user->permissions as $permission)
                                            <li>{{ $permission->name }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div>
                                    <strong>Permissions Through Roles:</strong>
                                    <ul>
                                        @foreach($user->getPermissionsViaRoles() as $permission)
                                            <li>{{ $permission->name }}</li>
                                        @endforeach
                                    </ul>
                                </div> --}}
                                <div style="max-height: 200px; overflow-y: auto;">
                                    <strong>Direct Permissions:</strong>
                                    <ul>
                                        @foreach($user->permissions as $permission)
                                            <li>{{ $permission->name }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                
                                <div style="max-height: 200px; overflow-y: auto;">
                                    <strong>Permissions Through Roles:</strong>
                                    <ul>
                                        @foreach($user->getPermissionsViaRoles() as $permission)
                                            <li>{{ $permission->name }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                                
                            </td>
                            <td>
                                <td>
                                    <div class="d-flex" style="gap: 5px;">
                                        @can(Permissions::EDIT_USERS)
                                            <a href="{{ route('sysadmin.users.edit', $user->id) }}"
                                               class="btn btn-warning btn-sm">
                                                Edit
                                            </a>
                                        @endcan

                                        @can(Permissions::DELETE_USERS)
                                            <button
                                                type="button"
                                                x-data
                                                x-on:click="if (confirm('Are you sure?')) { $wire.deleteUser({{ $user->id }}) }"
                                                class="btn btn-danger btn-sm"
                                            >
                                                Delete
                                            </button>
                                        @endcan

                                        @can(Permissions::EDIT_USERS)
                                            <a href="{{ route('sysadmin.users.permissions', $user->id) }}"
                                               class="btn btn-info btn-sm">
                                                Permissions
                                            </a>
                                        @endcan
                                    </div>
                                </td>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5">No users found.</td>
                        </tr>
                        @endforelse
                 
                </tbody>
            </table>
        </div>

        <div class="card-footer">
            <div class="mt-3">
                {{ $users->links() }} <!-- Livewire Pagination Links -->
            </div>
        </div>
    </div>
</div>


