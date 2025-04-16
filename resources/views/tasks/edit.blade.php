@extends('layouts.app2')
@include('inc.style')
@include('inc.navbar')

@section('content')
@include('inc.title')

<head>
    <style>
    body {
        font-family: 'Arial', sans-serif;
        background-color: #f8f9fa;
        color: #495057;
    }

    .container {
        margin-top: 50px;
        background-color: #ffffff;
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        font-weight: bold;
    }

    input[type="text"],
    input[type="date"],
    select,
    textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        box-sizing: border-box;
        margin-top: 5px;
    }

    .error {
        margin-top: 5px;
    }

    button {
        background-color: #007bff;
        color: #ffffff;
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    button:hover {
        background-color: #0056b3;
    }
    </style>
</head>

<div class="container">
    <form action="{{route('tasks.update', $task)}}" method="post">
        @csrf
        <input type="hidden" name="userstory_id" value="{{ $task->userstory_id }}">

        <div class="form-group">
            <label for="title">Task Name:</label>
            <input type="text" name="title" value="{{$task->title}}">
            <div class="error">
                <font color="red" size="2">{{ $errors->first('title') }}</font>
            </div>
        </div>

        <div class="form-group">
            <label for="description">Description:</label>
            <input type="text" name="description" value="{{$task->description}}">
            <div class="error">
                <font color="red" size="2">{{ $errors->first('description') }}</font>
            </div>
        </div>

        <div class="form-group" style="display: flex; justify-content: space-between;">
            <div style="width: 48%;">
                <label for="user_name">Assigned to:</label>
                <select name="user_names[]" multiple>
                    @foreach($teamlist as $teammember)
                    <option value="{{ $teammember['username'] }}"
                        {{ (old('user_names') && in_array($teammember['username'], old('user_names')) ? 'selected' : '') }}>
                        {{ $teammember['username'] }} (Team: {{ $teammember['team_name'] }})
                    </option>
                    @endforeach
                </select>
                <br><br>
            </div>

            <div style="width: 48%;">
                <label for="status_id">Status:</label>
                <select name="status_id" id="status_id" class="form-control">
                    <option value="" selected disabled>Select</option>
                    @foreach($statuses as $status)
                    <option value="{{ $status->id }}" @if($task->status_id == $status->id) selected @endif>
                        {{ $status->title }}</option>
                    @endforeach
                </select>
                <div class="error">
                    <font color="red" size="2">{{ $errors->first('status_id') }}</font>
                </div>
            </div>
        </div>

        <div class="form-group" style="display: flex; justify-content: space-between;">
            <div style="width: 48%;">
                <label for="start_date">Start Date:</label>
                <input type="date" name="start_date" value="{{$task->start_date}}">
                <div class="error">
                    <font color="red" size="2">{{ $errors->first('start_date') }}</font>
                </div>
                {{ $sprint->sprint_name }} Start Date: {{ date('d F Y', strtotime($sprint->start_sprint)) }}
            </div>

            <div style="width: 48%;">
                <label for="end_date">End Date:</label>
                <input type="date" name="end_date" value="{{$task->end_date}}">
                <div class="error">
                    <font color="red" size="2">{{ $errors->first('end_date') }}</font>
                </div>
                {{ $sprint->sprint_name }} End Date: {{ date('d F Y', strtotime($sprint->end_sprint)) }}
            </div>
        </div>

        <div class="form-group">
            <button type="submit">Update Task</button>
        </div>
    </form>
</div>
@endsection