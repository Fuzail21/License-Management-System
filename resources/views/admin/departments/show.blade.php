@extends('layouts.app')

@section('title', 'Department Details')
@section('page-title', 'Department: ' . $department->name)


@push('styles')
    <style>
        .badge-status {
            padding: 0.35rem 0.65rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 0.375rem;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }

        .badge-active {
            background-color: #10b981 !important;
            color: white !important;
        }

        .badge-inactive {
            background-color: #6b7280 !important;
            color: white !important;
        }

        .badge-expired {
            background-color: #ef4444 !important;
            color: white !important;
        }

        .badge-expiring {
            background-color: #f59e0b !important;
            color: white !important;
        }

        .badge-suspended {
            background-color: #8b5cf6 !important;
            color: white !important;
        }
    </style>
@endpush

@section('content')
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Department Information</h5>
                    <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-warning btn-sm">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-3">Name:</dt>
                        <dd class="col-sm-9">{{ $department->name }}</dd>

                        <dt class="col-sm-3">Description:</dt>
                        <dd class="col-sm-9">{{ $department->description ?? 'N/A' }}</dd>

                        <dt class="col-sm-3">Status:</dt>
                        <dd class="col-sm-9">
                            <x-status-badge :status="$department->status->value" />
                        </dd>

                        <dt class="col-sm-3">Created:</dt>
                        <dd class="col-sm-9">{{ $department->created_at->format('M d, Y h:i A') }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Users in this Department</h5>
            <a href="{{ route('admin.users.create', ['department_id' => $department->id]) }}"
                class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Add User
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Designation</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($department->users as $user)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.users.show', $user) }}" class="text-decoration-none">
                                        {{ $user->name }}
                                    </a>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? 'N/A' }}</td>
                                <td>{{ $user->designation ?? 'N/A' }}</td>
                                <td>
                                    <x-status-badge :status="$user->status->value" />
                                </td>
                                <td>
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info btn-action">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No users in this department</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection