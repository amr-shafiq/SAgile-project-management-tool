@extends('layouts.app2')

@include('inc.style')


@section('dashboard')

@foreach($projects as $project)
        <li>
            <a href="{{ route('projects.edit', [$project]) }}">
             {{ $project->proj_name }} 
            </a>
                     
        </li>
@endforeach
        
@if($projects->isEmpty())
     No project.
@endif
@endsection



@section('content')
<br><br><br>
    <a href="{{route('profeature.index')}}" class="button">Project List</a>
    <a href="{{route('userstory.index')}}" class="button">User Stories</a>
<br><br><br>
<table>
<tr>
    <th>ID</th>
    <th>User Stories</th>
    <th>Description</th>
    <th>Priority</th>
    <th>Status</th>
    <th>Edit</th>
</tr>

@foreach($userstories as $userstory)
<tr> 
    <th>
       {{ $userstory->u_id }}
    </th>

    <th>
    {{ $userstory->user_story }}
        
    </th>

    <th>
      {{ $userstory->means }}
    </th>

    <th>
      {{ $userstory->prio_story }}
    </th>

    <th>
      {{ $userstory->title }}
    </th>


    <th>
    <a href="{{route('userstory.edit', [$userstory->u_id])}}">
        Edit
    </a>
    </th>

    <th>
        <button type="submit"><a href="{{route('userstory.destroy', $userstory)}}" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this userstory?');">Delete</button>
    </th>
</tr>
@endforeach
</table>
<br><br><br>

<button type="submit"><a href="{{route('userstory.create')}}">Add User Stories</a></button>

@endsection