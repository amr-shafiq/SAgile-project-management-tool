<?php

namespace App\Http\Controllers;

use App\Task;
use App\Team;
use App\TeamMapping;
use App\Status;
use App\UserStory;
use App\User;
use App\Sprint;
use App\Project;
use App\Http\Controllers\Auth;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    //Main Sprint Page
    public function index($userstory_id)
    {
        //Get the project where user's team name(s) is the same with project's team name
        $user = \Auth::user();
        $teammapping = \App\TeamMapping::where('username', '=', $user->username)->pluck('team_name')->toArray(); // use pluck() to retrieve an array of team names
        $pro = \App\Project::whereIn('team_name', $teammapping)->get(); // use whereIn() to retrieve the projects that have a team_name value in the array

        //Get the task that is related to the userstory 
        $tasks = Task::where('userstory_id', $userstory_id)->get();

        //Get the userstory that is passed in the parameter
        $userstory = UserStory::where('u_id', $userstory_id)->first();
        $statuses = Status::all();

        return view('tasks.index')
            ->with('userstory_id', $userstory_id)
            ->with('tasks', $tasks)
            ->with('statuses', $statuses)
            ->with('title', 'Tasks for ' . $userstory->user_story)
            ->with('pros', $pro);
    }

    public function indexCalendar($userstory_id){

        //Get the project where user's team name(s) is the same with project's team name
        $user = \Auth::user();
        $teammapping = \App\TeamMapping::where('username', '=', $user->username)->pluck('team_name')->toArray(); // use pluck() to retrieve an array of team names
        $pro = \App\Project::whereIn('team_name', $teammapping)->get(); // use whereIn() to retrieve the projects that have a team_name value in the array

        //Get the task that is related to the userstory 
        $tasks = Task::where('userstory_id', $userstory_id)->get();
        //Get the userstory that is passed in the parameter
        $userstory = UserStory::where('u_id', $userstory_id)->first();

        return view('tasks.calendarTask');
        // ->with('userstory_id', $userstory_id)
        // ->with('tasks', $tasks)
        // ->with('title', 'Tasks for ' . $userstory->user_story)
        // ->with('pros', $pro);
    }

    //index Kanban Board
    public function indexKanbanBoard() 
    {
        //the function will send the required data to the kanban board to display
        //the kanban board will display all tasks that is related to the user's team's project

        $user = \Auth::user();
        $teammapping = \App\TeamMapping::where('username', '=', $user->username)->pluck('team_name')->toArray(); // use pluck() to retrieve an array of team names
        $pro = \App\Project::whereIn('team_name', $teammapping)->get(); // use whereIn() to retrieve the projects that have a team_name value in the array
        
        return view('tasks.kanban')
            ->with('pro', $pro);
    }

    //view specific Kanban Board
    public function kanbanIndex($proj_id, $sprint_id)
    {
        $sprint = Sprint::where('sprint_id', $sprint_id)->first();
        $project = Project::where('id', $proj_id)->first();
        $statuses = Status::where('project_id', $proj_id)->get();
        $tasks = Task::where("proj_id", $proj_id)->where("sprint_id", $sprint_id)->get();

        // Group tasks by status id
        $tasksByStatus = [];
        foreach ($tasks as $task) {
            $tasksByStatus[$task->status_id][] = $task;
        }

        return view('kanban.index', ['statuses' => $statuses, 'tasksByStatus' => $tasksByStatus, 'sprint' => $sprint, 'project' => $project]);
    }


    public function updateKanbanBoard(Request $request, $id) {
        $task = Task::find($id);
      
        // Check if the task exists
        if (!$task) {
          return response()->json(['message' => 'Task not found'], 404);
        }
      
        // Update the task with the new status_name
        $task->status_name = $request->input('status_name');
        $task->save();
      
        return response()->json(['message' => 'Task updated successfully']);
      }

    public function getTaskDescription($task_id)
    {
        $task = Task::find($task_id);

        if (!$task) {
            // Task not found, handle accordingly
            return response()->json(['message' => 'Task not found'], 404);
        }

        return response()->json(['description' => $task->description]);
    }
      

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($userstory_id)
    {
        $userstory = UserStory::where('u_id', $userstory_id)->first();

        //get the project and sprint related to the task 
        $sprint = Sprint::where('sprint_id', $userstory->sprint_id)->first();
        $project = Project::where('proj_name', $sprint->proj_name)->first();

        //get the team for the project //tukar get kalau nak ambik semua
        $team = Project::where('proj_name', $project->proj_name)->get();

        //get the list of team members for the team //for each team
        //$teamlist = TeamMapping::where('team_name', $team->team_name)->get();
        $userTeams = [];

        // Iterate through each team
        foreach ($team as $teamItem) {
            // Get the list of team members for the current team
            $teamlist = TeamMapping::where('team_name', $teamItem->team_name)->get();

            // Now $teamlist contains the team members for the current team
            foreach ($teamlist as $teammember) {
                // Access individual team member properties like $teammember->username
                // Do something with each team member

                // Save username and team_name in a 2D array
                $userTeams[] = [
                    'username' => $teammember->username,
                    'team_name' => $teamItem->team_name,
                ];
            }
        }


        // Get the proj_name from the project
        $team_name = $project->team_name;
        
        //send the existing statuses for the project related   
        $status = Status::where('project_id', $project->id)->get();

        return view('tasks.create')
        ->with('title', 'Create Task for '. $userstory->user_story)
        ->with('statuses', $status)
        ->with('teamlist',  $userTeams)
        ->with('sprint', $sprint)
        ->with('userstory_id', $userstory_id);
    }

    // Redirect to Create Task Page
    public function createTask(Request $request)
    {
        $sprintId = $request->input('sprintId');
        $statusId = $request->input('statusId');
        $sprint = Sprint::where('sprint_id', $sprintId)->first();
        $sprintProjName = $sprint->proj_name;
        $sprintProj = Project::where('proj_name', $sprintProjName)->first();
        $sprintProjId = $sprintProj->id;
 
        $userStories = UserStory::where('sprint_id', $sprintId)->get();
        $userList = User::all();

        //get the team for the project //tukar get kalau nak ambik semua
        $team = Project::where('proj_name', $sprintProjName)->get();
        // Iterate through each team
        foreach ($team as $teamItem) {
            // Get the list of team members for the current team
            $teamlist = TeamMapping::where('team_name', $teamItem->team_name)->get();

            // Now $teamlist contains the team members for the current team
            foreach ($teamlist as $teammember) {
                // Access individual team member properties like $teammember->username
                // Do something with each team member

                // Save username and team_name in a 2D array
                $userTeams[] = [
                    'username' => $teammember->username,
                    'team_name' => $teamItem->team_name,
                ];
            }
        }

        return view('kanban.addTask', [
            'userStories' => $userStories,
            'userList' => $userList,
            'sprint_id' => $sprintId,
            'status_id' => $statusId,
            'sprintProjId' => $sprintProjId,
            'sprint' => $sprint,
            'teamlist' =>  $userTeams
        ]);
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function store(Request $request)
    {
        $userstory = UserStory::where('u_id', $request->userstory_id)->first();

        //get the project and sprint related to the task 

        if ($request->isKanban == "1") {
            $sprint = Sprint::where('sprint_id', $request->sprint_id)->first();
        } else {
            $sprint = Sprint::where('sprint_id', $userstory->sprint_id)->first(); 
        }

        $project = Project::where('proj_name', $sprint->proj_name)->first();

        //validate the request
        $validation = $request->validate([
            //validate for existing task names
            'title' => 'required|unique:tasks,title,NULL,id,userstory_id,'.$request->userstory_id,
            'description' => 'required',

            //validate that start of task should be after or equal the sprint's start date
            'start_date' => 'required|date|after_or_equal:'.$sprint->start_sprint,

            //validate that end of task should be before or equal the sprint's end date
            'end_date' => 'required|date|before_or_equal:'.$sprint->end_sprint.'|after_or_equal:start_date',

        ], [
            'title.required' => '*The Task Name is required',
            'title.unique' => '*There is already an existing task in the userstory with the same name',
            'description.required' => '*The Description is required',
            'start_date.required' => '*The Start Date is required',
            'start_date.after_or_equal' => '*The Start Date must be equal to or after the sprint start date',
            'end_date.required' => '*The End Date is required',
            'end_date.before_or_equal' => '*The End Date must be equal to or before the sprint end date',
            'end_date.after_or_equal' => '*The End Date must be equal to or after the Start Date',
        ]);

        //assign request values to new task 
        $task = new Task();
        $task->userstory_id = $request->userstory_id;
        $task->title = $request->title;
        $task->description = $request->description;
        $task->user_names = json_encode($request->user_names);
        $task->status_id = $request->status_id;
        $task->start_date = $request->start_date;
        $task->end_date = $request->end_date;
        $task->proj_id = $project->id;
        $task->sprint_id = $sprint->sprint_id;
        $task->newTask_update = now()->timezone("Asia/Kuala_Lumpur")->toDateString();
        $task->save();

        $tasks = Task::where('userstory_id', $request->userstory_id)->get();
        //Get the project where user's team name(s) is the same with project's team name
        $user = \Auth::user();
        $teammapping = \App\TeamMapping::where('username', '=', $user->username)->pluck('team_name')->toArray(); // use pluck() to retrieve an array of team names
        $pro = \App\Project::whereIn('team_name', $teammapping)->get(); // use whereIn() to retrieve the projects that have a team_name value in the array
        $statuses = Status::all();

        if ($request->isKanban == "1") {
            return redirect()->route('sprint.kanbanPage', ['proj_id' => $request->sprintProjId, 'sprint_id' => $request->sprint_id]);
        } else {
            return redirect()->route('tasks.index', ['u_id' => $userstory->u_id])
            ->with('title', 'Tasks for ' . $userstory->user_story)
            ->with('success', 'Task has successfully been created!')
            ->with('task', $tasks)
            ->with('statuses', $statuses)
            ->with('userstory_id', $userstory->u_id)
            ->with('pros', $pro);
        }
    }

    public function sync(Request $request)
    {
        $this->validate(request(), [
            'columns' => ['required', 'array']
        ]);

        foreach ($request->columns as $status) {
            foreach ($status['tasks'] as $i => $task) {
                $order = $i + 1;
                if ($task['status_id'] !== $status['id'] || $task['order'] !== $order) {
                    request()->user()->tasks()
                        ->find($task['id'])
                        ->update(['status_id' => $status['id'], 'order' => $order]);
                }
            }
        }

        return $request->user()->statuses()->with('tasks')->get();
    }

    public function show(Task $task)
    {
        //
    }

    public function edit($task_id)
    {
        //Get the current task
        $task = Task::where('id', $task_id)->first();
        $userstory = UserStory::where('u_id', $task->userstory_id)->first();
        $sprint = Sprint::where('sprint_id', $task->sprint_id)->first();
        $project = Project::where('id', $task->proj_id)->first();

        //get the team for the project
        $team = Project::where('proj_name', $project->proj_name)->get();

        //get the list of team members for the team
        //$teamlist = TeamMapping::where('team_name', $team->team_name)->get();

        // Get the proj_name from the project
        //$team_name = $project->team_name;

        $userTeams = [];

        // Iterate through each team
        foreach ($team as $teamItem) {
            // Get the list of team members for the current team
            $teamlist = TeamMapping::where('team_name', $teamItem->team_name)->get();

            // Now $teamlist contains the team members for the current team
            foreach ($teamlist as $teammember) {
                // Access individual team member properties like $teammember->username
                // Do something with each team member

                // Save username and team_name in a 2D array
                $userTeams[] = [
                    'username' => $teammember->username,
                    'team_name' => $teamItem->team_name,
                ];
            }
        }

        
        //send the existing statuses for the project related   
        $status = Status::where('project_id', $project->id)->get();

        return view('tasks.edit')
            ->with('title', 'Edit '. $task->title . ' in '. $userstory->user_story)
            ->with('project', $project)
            ->with('sprint', $sprint)
            ->with('statuses', $status)
            ->with('teamlist', $userTeams)
            ->with('task', $task);
    }

    public function update(Request $request, Task $task)
    {
        //Get the current task
        $userstory = UserStory::where('u_id', $task->userstory_id)->first();
        $sprint = Sprint::where('sprint_id', $task->sprint_id)->first();
        $project = Project::where('id', $task->proj_id)->first();

        //validate the request
        $validation = $request->validate([
            //validate for existing task names
            'title' => 'required|unique:tasks,title,'.$task->id.',id,userstory_id,'.$userstory->u_id,
            'description' => 'required',

            //validate that start of task should be after or equal the sprint's start date
            'start_date' => 'required|date|after_or_equal:'.$sprint->start_sprint,

            //validate that end of task should be before or equal the sprint's end date
            'end_date' => 'required|date|before_or_equal:'.$sprint->end_sprint.'|after_or_equal:start_date',

        
        ], [
            'title.required' => '*The Task Name is required',
            'title.unique' => '*There is already an existing task in the userstory with the same name',
            'description.required' => '*The Description is required',
            'start_date.required' => '*The Start Date is required',
            'start_date.after_or_equal' => '*The Start Date must be equal to or after the sprint start date',
            'end_date.required' => '*The End Date is required',
            'end_date.before_or_equal' => '*The End Date must be equal to or before the sprint end date',
            'end_date.after_or_equal' => '*The End Date must be equal to or after the Start Date',
        ]);

        $task->title = $request->title;
        $task->description = $request->description;
        $task->user_names = json_encode($request->user_names);
        $task->status_id = $request->status_id;
        $task->start_date = $request->start_date;
        $task->end_date = $request->end_date;
        $task->newTask_update = now()->timezone("Asia/Kuala_Lumpur")->toDateString();
        $task->save();

        $tasks = Task::where('userstory_id', $task->userstory_id)->get();

        //Get the project where user's team name(s) is the same with project's team name
        $user = \Auth::user();
        $teammapping = \App\TeamMapping::where('username', '=', $user->username)->pluck('team_name')->toArray(); // use pluck() to retrieve an array of team names
        $pro = \App\Project::whereIn('team_name', $teammapping)->get(); // use whereIn() to retrieve the projects that have a team_name value in the array
        $statuses = Status::all();

        if ($request->isKanban == "1") {
            return redirect()->route('sprint.kanbanPage', ['proj_id' => $request->sprintProjId, 'sprint_id' => $request->sprint_id]);
        } else {
            return redirect()->route('tasks.index', ['u_id' => $userstory->u_id])
            ->with('title', 'Tasks for ' . $userstory->user_story)
            ->with('success', 'Task has successfully been updated!')
            ->with('task', $tasks)
            ->with('statuses', $statuses)
            ->with('userstory_id', $userstory->u_id)
            ->with('pros', $pro);
        }

    }

    // Redirect to updateTaskPage
    public function updateTaskPage($taskId)
    {
        $task = Task::findOrFail($taskId);
        $userList = User::all();
        $status_id = $task->status_id;
        $sprint_id = $task->sprint_id;
        $sprintProjId = $task->proj_id;  // Add this line
        $userStories = UserStory::where('sprint_id', $task->sprint_id)->get();
        $sprint = Sprint::where('sprint_id', $sprint_id)->first();
        $sprintProjName = $sprint->proj_name;

        //get the team for the project //tukar get kalau nak ambik semua
        $team = Project::where('proj_name', $sprintProjName)->get();
        // Iterate through each team
        foreach ($team as $teamItem) {
            // Get the list of team members for the current team
            $teamlist = TeamMapping::where('team_name', $teamItem->team_name)->get();

            // Now $teamlist contains the team members for the current team
            foreach ($teamlist as $teammember) {
                // Access individual team member properties like $teammember->username
                // Do something with each team member

                // Save username and team_name in a 2D array
                $userTeams[] = [
                    'username' => $teammember->username,
                    'team_name' => $teamItem->team_name,
                ];
            }
        }

        return view('kanban.updateTask', [
            'task' => $task,
            'userStories' => $userStories,
            'userList' => $userList,
            'status_id' => $status_id,
            'sprint_id' => $sprint_id,
            'sprintProjId' => $sprintProjId,  
            'sprint' => $sprint,
            'teamlist' =>  $userTeams
        ]);
    }

    public function destroy(Task $task)
    {
        $userstory = UserStory::where('u_id', $task->userstory_id)->first();
        $tasks = Task::where('userstory_id', $task->userstory_id)->get();

        //Get the project where user's team name(s) is the same with project's team name
        $user = \Auth::user();
        $teammapping = \App\TeamMapping::where('username', '=', $user->username)->pluck('team_name')->toArray(); // use pluck() to retrieve an array of team names
        $pro = \App\Project::whereIn('team_name', $teammapping)->get(); // use whereIn() to retrieve the projects that have a team_name value in the array
        $statuses = Status::all();


        $task->delete();

        return redirect()->route('tasks.index', ['u_id' => $userstory->u_id])
        ->with('title', 'Tasks for ' . $userstory->user_story)
        ->with('success', 'Task has successfully been deleted!')
        ->with('task', $tasks)
        ->with('statuses', $statuses)
        ->with('userstory_id', $userstory->u_id)
        ->with('pros', $pro);
    }

    // Delete a task
    public function deleteTask(Request $request)
    {
        $taskId = $request->input('taskId');

        try {
            // Find and delete the task
            Task::destroy($taskId);

            return response()->json(['success' => true, 'message' => 'Task deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Error deleting task'], 500);
        }
    }


}