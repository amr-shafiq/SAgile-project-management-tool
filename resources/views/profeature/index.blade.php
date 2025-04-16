@extends('layouts.app2')

@include('inc.style')
@include('inc.success')
@include('inc.dashboard')
@include('inc.navbar')

@section('content')

@can('isProjectManager')
@include('inc.title')
<br>

<table class="table">
    <thead class="table-light">
        <!-- Table Header -->
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Team</th>
            <th>Edit</th>
            <th>Delete</th>
            <th>Backlog</th>
            <th>Sprint</th>
            <th>Forum</th>
            <th>Defect Tracking</th>
        </tr>
    </thead>

    <!-- Check if projects exist -->
    @if ($pros->isEmpty())
        <tr>
            <td colspan="10">
                <h3>There are no projects yet:</h3>
            </td>
        </tr>
    @else
        <!-- Loop through existing projects -->
        @foreach($pros as $project)
            <tr>
                <!-- Display project details -->
                <td>{{ $project->proj_name }}</td>
                <td>{{ $project->proj_desc }}</td>
                <td>{{ date('d F Y', strtotime($project->start_date)) }}</td>
                <td>{{ date('d F Y', strtotime($project->end_date)) }}</td>
                <!-- Display team name -->
                <td><a href="{{ route('team.index') }}">{{ $project->team_name }}</a></td>
                <!-- Edit link -->
                <td><a href="{{ route('projects.edit', $project->id) }}">Edit</a></td>

                <!-- Delete form -->
                <td>
                    <form action="{{ route('projects.destroy', $project->id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <div class="mt-3 mb-1">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this project?');">Delete</button>
                        </div>
                    </form>
                </td>

                <!-- Links related to the project -->
                <td><a href="{{ route('backlog.index', $project->id) }}">View</a></td>
                <td><a href="{{ action('ProductFeatureController@index2', $project->proj_name) }}">View</a></td>
                <td><a href="{{ route('forum.index', ['projectId' => $project->id]) }}">View</a></td>
                <td><a href="">View</a></td>
            </tr>
        @endforeach
    @endif
</table>

<h3 class="text-danger mt-4"> Warning </h3>
<p class="text-danger mt-4">DO NOT CREATE THE PROJECT FOR ANYONE ELSE AND ONLY PICK THE ONE THAT YOU HAVE BEEN ASSIGNED!</p>
<h6> Please create the project only for you and your assigned team as you have logged into the system. Otherwise, the table cannot be updated normally.</h6>
<p>For example, if your name is Abu and your assigned team is Team1, you can create project but be sure to click on your team, Team1.</p>
<br><br><br>

<!-- Button to create a new project -->
<button type="submit"><a href="{{ route('projects.create') }}">Add Project</a></button>
@endcan

@can('isUser')
@include('inc.title')
<br>

<table class="table">
    <thead class="table-light">
        <!-- Table Header -->
        <tr>
            <th>Title</th>
            <th>Description</th>
            <th>Start Date</th>
            <th>End Date</th>
            <th>Team</th>
            <th>Edit</th>
            <th>Backlog</th>
            <th>Sprint</th>
            <th>Forum</th>
            <th>Defect Tracking</th>
        </tr>
    </thead>

    <!-- Check if projects exist -->
    @if ($pros->isEmpty())
        <tr>
            <td colspan="10">
                <h3>There are no projects yet:</h3>
            </td>
        </tr>
    @else
        <!-- Loop through existing projects -->
        @foreach($pros as $project)
            <tr>
                <!-- Display project details -->
                <td>{{ $project->proj_name }}</td>
                <td>{{ $project->proj_desc }}</td>
                <td>{{ date('d F Y', strtotime($project->start_date)) }}</td>
                <td>{{ date('d F Y', strtotime($project->end_date)) }}</td>
                <!-- Display team name -->
                <td><a href="{{ route('team.index') }}">{{ $project->team_name }}</a></td>
                <!-- Edit link -->
                <td><a href="{{ route('projects.edit', $project->id) }}">Edit</a></td>


                <!-- Links related to the project -->
                <td><a href="{{ route('backlog.index', $project->id) }}">View</a></td>
                <td><a href="{{ action('ProductFeatureController@index2', $project->proj_name) }}">View</a></td>
                <td><a href="{{ route('forum.index', ['projectId' => $project->id]) }}">View</a></td>
                <td><a href="">View</a></td>
            </tr>
        @endforeach
    @endif
</table>

<h3 class="text-danger mt-4"> Warning </h3>
<p class="text-danger mt-4">DO NOT CREATE THE PROJECT FOR ANYONE ELSE AND ONLY PICK THE ONE THAT YOU HAVE BEEN ASSIGNED!</p>
<h6> Please create the project only for you and your assigned team as you have logged into the system. Otherwise, the table cannot be updated normally.</h6>
<p>For example, if your name is Abu and your assigned team is Team1, you can create project but be sure to click on your team, Team1.</p>
<br><br><br>

<!-- Button to create a new project -->
<button type="submit"><a href="{{ route('projects.create') }}">Add Project</a></button>
@endcan

@can('isAdmin')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">{{ __('Access Denied') }}</div>

                <div class="card-body">
                    <h1>403</h1>
                    <p>{{ __('You do not have permission to access this page.') }}</p>
                    <a href="{{ url('/home') }}" class="btn btn-primary">Go to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endcan



@endsection
