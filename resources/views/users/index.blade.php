@extends('layouts.master')

@section('content')
    <main>
        <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
            <div class="container-fluid px-4">
                <div class="page-header-content pt-4">
                    {{-- <div class="row align-items-center justify-content-between">
                    <div class="col-auto mt-4">
                        <h1 class="page-header-title">
                            <div class="page-header-icon"><i data-feather="tool"></i></div>
                            Dropdown App Menu
                        </h1>
                        <div class="page-header-subtitle">Use this blank page as a starting point for creating new pages inside your project!</div>
                    </div>
                    <div class="col-12 col-fluid-auto mt-4">Optional page header content</div>
                </div> --}}
                </div>
            </div>
        </header>
        <!-- Main page content-->
        <div class="container-fluid mt-n10 px-4">
            <!-- Content Wrapper. Contains page content -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <section class="content-header">
                    {{-- <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>    </h1>
          </div>
        </div>
      </div><!-- /.container-fluid --> --}}
                </section>

                <!-- Main content -->
                <section class="content">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h3 class="card-title">List of User</h3>
                                    </div>

                                    <!-- /.card-header -->
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-12 mb-3">
                                                <button type="button" class="btn btn-dark btn-sm mb-2"
                                                    data-bs-toggle="modal" data-bs-target="#modal-add">
                                                    <i class="fas fa-plus-square"></i>
                                                </button>

                                                <!-- Modal -->
                                                <div class="modal fade" id="modal-add" tabindex="-1"
                                                    aria-labelledby="modal-add-label" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title" id="modal-add-label">Add User</h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="{{ url('/user/store') }}" method="POST"
                                                                enctype="multipart/form-data">
                                                                @csrf
                                                                <div class="modal-body">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <!-- Name Field -->
                                                                            <div class="form-group mb-2">
                                                                                <label for="name">Full Name</label>
                                                                                <input type="text" class="form-control"
                                                                                    id="name" name="name"
                                                                                    placeholder="Enter Full Name" required>
                                                                            </div>

                                                                            <!-- Username Field -->
                                                                            <div class="form-group mb-2">
                                                                                <label for="username">Username</label>
                                                                                <input type="text" class="form-control"
                                                                                    id="username" name="username"
                                                                                    placeholder="Enter Username" required>
                                                                            </div>

                                                                            <div class="form-group mb-2">
                                                                                <label for="job_title">Job Title</label>
                                                                                <select class="form-control form-control-sm"
                                                                                    name="job_title" id="job_title"
                                                                                    required>
                                                                                    <option value="">-- Select Job
                                                                                        Title --</option>
                                                                                    <option value="Department Head">
                                                                                        Department Head</option>
                                                                                    <option value="Section Head">Section
                                                                                        Head</option>
                                                                                    <option value="Staff">Staff</option>
                                                                                </select>
                                                                            </div>

                                                                            <!-- Image Field -->
                                                                            <div class="form-group mb-2">
                                                                                <label for="img">Image</label>
                                                                                <input name="img" type="file"
                                                                                    class="form-control" id="img"
                                                                                    required>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <!-- Role Dropdown -->
                                                                            <div class="form-group mb-2">
                                                                                <label for="role">Role</label>
                                                                                <select name="role" id="role"
                                                                                    class="form-control">
                                                                                    <option value="">- Please Select
                                                                                        Role -</option>
                                                                                    @foreach ($dropdown as $role)
                                                                                        <option
                                                                                            value="{{ $role->code_format }}">
                                                                                            {{ $role->name_value }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>

                                                                            <div class="form-group mb-2">
                                                                                <label for="department">Department</label>
                                                                                <select class="form-control form-control-sm"
                                                                                    name="department" id="department"
                                                                                    required>
                                                                                    <option value="">-- Select
                                                                                        Department --</option>
                                                                                    @foreach ($dept as $item)
                                                                                        <option
                                                                                            value="{{ $item->name_value }}">
                                                                                            {{ $item->name_value }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>


                                                                            <!-- Email Field -->
                                                                            <div class="form-group mb-2">
                                                                                <label for="email">Email</label>
                                                                                <input type="email" class="form-control"
                                                                                    id="email" name="email"
                                                                                    placeholder="Enter Email Address"
                                                                                    required>
                                                                            </div>

                                                                            <!-- Password Field -->
                                                                            <div class="form-group mb-2">
                                                                                <label for="password">Password</label>
                                                                                <input type="password" class="form-control"
                                                                                    id="password" name="password"
                                                                                    placeholder="Enter Password" required>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-dark"
                                                                        data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit"
                                                                        class="btn btn-primary">Submit</button>
                                                                </div>
                                                            </form>


                                                        </div>
                                                    </div>
                                                </div>


                                                @include('partials.alert')
                                            </div>
                                            <table id="tableUser" class="table-bordered table-striped table">
                                                <thead>
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Username</th>
                                                        <th>Name</th>
                                                        <th>Email</th>
                                                        <th>Role</th>
                                                        <th>Job Title</th>
                                                        <th>Department</th>
                                                        <th>Last Login</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody></tbody>
                                            </table>

                                            @foreach ($user as $data)
                                                {{-- Modal Update --}}
                                                <div class="modal fade" id="modal-update{{ $data->id }}"
                                                    tabindex="-1" aria-labelledby="modal-update{{ $data->id }}-label"
                                                    aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h4 class="modal-title"
                                                                    id="modal-update{{ $data->id }}-label">Edit User
                                                                </h4>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="{{ url('/user/update/' . $data->id) }}"
                                                                enctype="multipart/form-data" method="POST">
                                                                @csrf
                                                                @method('patch')
                                                                <div class="modal-body">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <!-- Full Name Field -->
                                                                            <div class="form-group mb-2">
                                                                                <label for="name">Full Name</label>
                                                                                <input type="text" class="form-control"
                                                                                    id="name" name="name"
                                                                                    value="{{ $data->name }}" required>
                                                                            </div>

                                                                            <!-- Username Field -->
                                                                            <div class="form-group mb-2">
                                                                                <label for="username">Username</label>
                                                                                <input type="text" class="form-control"
                                                                                    id="username" name="username"
                                                                                    value="{{ $data->username }}"
                                                                                    required>
                                                                            </div>

                                                                            <!-- Job Title Field -->
                                                                            <div class="form-group mb-2">
                                                                                <label for="job_title">Job Title</label>
                                                                                <input type="text" class="form-control"
                                                                                    id="job_title" name="job_title"
                                                                                    value="{{ $data->job_title }}"
                                                                                    required>
                                                                            </div>

                                                                            <!-- Image Field -->
                                                                            <div class="form-group mb-2">
                                                                                <label for="img">Image</label>
                                                                                <input type="file" class="form-control"
                                                                                    id="img" name="img">
                                                                                @if ($data->img)
                                                                                    <small>Current Image: <a
                                                                                            href="{{ asset($data->img) }}"
                                                                                            target="_blank">View</a></small>
                                                                                @endif
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <!-- Role Dropdown -->
                                                                            <div class="form-group mb-2">
                                                                                <label for="role">Role</label>
                                                                                <select name="role" id="role"
                                                                                    class="form-control">
                                                                                    <option value="">- Please Select
                                                                                        Role -</option>
                                                                                    @foreach ($dropdown as $role)
                                                                                        <option
                                                                                            value="{{ $role->name_value }}"
                                                                                            {{ $data->role == $role->name_value ? 'selected' : '' }}>
                                                                                            {{ $role->name_value }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </div>

                                                                            <!-- Department Field -->
                                                                            <div class="form-group mb-2">
                                                                                <label for="department">Department</label>
                                                                                <input type="text" class="form-control"
                                                                                    id="department" name="department"
                                                                                    value="{{ $data->department }}"
                                                                                    required>
                                                                            </div>

                                                                            <!-- Email Field -->
                                                                            <div class="form-group mb-2">
                                                                                <label for="email">Email</label>
                                                                                <input type="email" class="form-control"
                                                                                    id="email" name="email"
                                                                                    value="{{ $data->email }}" required>
                                                                            </div>

                                                                            <!-- Password Field -->
                                                                            <div class="form-group mb-2">
                                                                                <label for="password">New Password</label>
                                                                                <input type="password"
                                                                                    class="form-control" id="password"
                                                                                    name="password"
                                                                                    placeholder="Leave blank to keep current password">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-dark"
                                                                        data-bs-dismiss="modal">Close</button>
                                                                    <button type="submit"
                                                                        class="btn btn-primary">Update</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                                {{-- Modal Update --}}

                                                {{-- Modal Revoke --}}
                                                <div class="modal fade" id="modal-revoke{{ $data->id }}">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <h4 class="modal-title">Revoke User Access</h4>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="{{ url('/user/revoke/' . $data->id) }}"
                                                                enctype="multipart/form-data" method="GET">
                                                                @csrf
                                                                <div class="modal-body">
                                                                    <div class="form-group mb-2">
                                                                        Are you sure you want to revoke <label
                                                                            for="email">{{ $data->email }}</label>?
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer justify-content-between">
                                                                    <button type="button"
                                                                        class="btn btn-dark btn-default"
                                                                        data-bs-dismiss="modal">Close</button>
                                                                    <input type="submit" class="btn btn-primary"
                                                                        value="Submit">
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Modal Revoke --}}
                                            @endforeach
                                        </div>
                                        <!-- /.card-body -->
                                    </div>
                                    <!-- /.card -->
                                </div>
                                <!-- /.col -->
                            </div>
                            <!-- /.row -->
                        </div>
                        <!-- /.container-fluid -->
                </section>
                <!-- /.content -->
            </div>
            <!-- /.content-wrapper -->
        </div>


    </main>
    <script>
        $(function() {
            $('#tableUser').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('user.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'username',
                        name: 'username'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'role',
                        name: 'role'
                    },
                    {
                        data: 'job_title',
                        name: 'job_title'
                    },
                    {
                        data: 'department',
                        name: 'department'
                    },
                    {
                        data: 'last_login',
                        name: 'last_login'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                responsive: true,
                lengthChange: false,
                autoWidth: false
            });
        });
    </script>
@endsection
