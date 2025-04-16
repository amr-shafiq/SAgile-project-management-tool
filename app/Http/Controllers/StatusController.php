<?php

namespace App\Http\Controllers;

use App\Status;
use App\User;
use App\Task;
use Illuminate\Http\Request;
use App\Http\Controllers\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class StatusController extends Controller
{
    public function index()
    {
        //Get the project where user's team name(s) is the same with project's team name
        $user = \Auth::user();
        $teammapping = \App\TeamMapping::where('username', '=', $user->username)->pluck('team_name')->toArray(); // use pluck() to retrieve an array of team names
        $pro = \App\Project::whereIn('team_name', $teammapping)->get(); // use whereIn() to retrieve the projects that have a team_name value in the array
        
        $projectID = $pro->pluck('id')->toArray(); // use pluck() to retrieve an array of project IDs
        $statuses = Status::whereIn('project_id', $projectID)->get();

        

        return view('status.index')->with ('statuses', $statuses)
            ->with('title', 'Status : List of Projects')
            ->with('pros', $pro);
    }

    //The index for specific projects' status
    public function indexProjectStatus($proj_id){

        //Get the project where user's team name(s) is the same with project's team name
        $user = \Auth::user();
        $teammapping = \App\TeamMapping::where('username', '=', $user->username)->pluck('team_name')->toArray(); // use pluck() to retrieve an array of team names
        $pro = \App\Project::whereIn('team_name', $teammapping)->get(); // use whereIn() to retrieve the projects that have a team_name value in the array
        
        $project = \App\Project::where('id', $proj_id)->first();
        $statuses = Status::where('project_id', $proj_id)->get();
         
        return view('status.project')->with ('statuses', $statuses)
            ->with('title', 'Status for ' . $project->proj_name)
            ->with('pros', $pro)
            ->with('project', $project);
    }

    public function create($proj_id)
    {

        $project = \App\Project::where('id', $proj_id)->first();
        return view('status.create')
            ->with('title', 'Create Status for ' . $project->proj_name)
            ->with('project', $project);
    }

    public function store(Request $request)
    {
        //validate the request
        $validation = $request->validate([
            'title' => 'required|unique:statuses',
        ],[
            'title.required' => '*The Status Name is required',
            'title.unique' => '*There is already an existing Status with the same name',
        ]);


        //assign the request parameters to new Status 
        $statuses = new Status();
        $statuses->title = $request->title;

        //Takes the title of status and changes it to lowercase and - when there is whitespace
        $slug = $request->title;
        $slug = Str::slug($slug, "-");
        $slug = strtolower($slug); 
        $statuses->slug = $slug;

        //gets the highest order in the status with the same project and adds 1 order higher to the current status
        $projectID = $request->project_id; 
        $highestOrder = DB::table('statuses')
                        ->select(DB::raw('MAX(`order`) AS `highest_order`'))
                        ->where('project_id', $projectID)
                        ->get()[0]->highest_order;

        $statuses->order = $highestOrder + 1;
        $statuses->project_id = $request->project_id;
        $statuses->save();


        //Get the project where user's team name(s) is the same with project's team name
        $user = \Auth::user();
        $teammapping = \App\TeamMapping::where('username', '=', $user->username)->pluck('team_name')->toArray(); // use pluck() to retrieve an array of team names
        $pro = \App\Project::whereIn('team_name', $teammapping)->get(); // use whereIn() to retrieve the projects that have a team_name value in the array
        
        $project = \App\Project::where('id', $request->project_id)->first();
        $statuses = Status::where('project_id', $request->project_id)->get(); 

        return redirect('/status/' . $request->project_id)
        ->with ('statuses', $statuses)
        ->with('pros', $pro)
        ->with('project', $project)
        ->with('title', 'Status for ' . $project->proj_name)
        ->with('success', 'Status has successfully been created for ' .$project->proj_name .'!');

    }

    public function show(Status $status)
    {
        // $status = new Status();
        // return view('statuses.show')->with ('statuses',$status->all());
    }

    public function edit(Status $status)
    {
        $user = \Auth::user();

        return view('status.edit')
            ->with('statuses', Status::all())
            ->with('status', $status);
    }

    public function update(Request $request, Status $statuses)
    {
        $statuses->title=$request->title;
        $statuses->slug=$request->slug;
        $statuses->order=$request->order;
        $statuses->user_id=$request->user_id;
        //$status->slug=$request->slug;
        $statuses->save(); 
    
        return redirect()->route('status.index', $statuses);
    }

    public function destroy(Status $status)
    {
        $projectID = $status->project_id;

        //Get the project where user's team name(s) is the same with project's team name
        $user = \Auth::user();
        $teammapping = \App\TeamMapping::where('username', '=', $user->username)->pluck('team_name')->toArray(); // use pluck() to retrieve an array of team names
        $pro = \App\Project::whereIn('team_name', $teammapping)->get(); // use whereIn() to retrieve the projects that have a team_name value in the array
        
        $project = \App\Project::where('id', $projectID)->first();
        $statuses = Status::where('project_id', $projectID)->get(); 
        
        $status->delete();

        return redirect('/status/' . $projectID)
        ->with ('statuses', $statuses)
        ->with('pros', $pro)
        ->with('project', $project)
        ->with('title', 'Status for ' . $project->proj_name)
        ->with('success', 'Status has successfully been deleted!');
    }

    //KanbanProcesses
    public function createStatus(Request $request)
    {
        // validate the request
        // $validation = $request->validate([
        //     'statusName' => 'required|unique:statuses,title',
        //     'sprintID' => 'required|exists:sprints,id',
        // ], [
        //     'statusName.required' => '*The Status Name is required',
        //     'statusName.unique' => '*There is already an existing Status with the same name',
        //     'sprintID.required' => '*The Sprint ID is required',
        //     'sprintID.exists' => '*The specified Sprint ID does not exist',
        // ]);

        // Extract data from the request
        $newLaneName = $request->input('statusName');
        $sprintID = $request->input('sprintID');
        $projectID = $request->input('project_id');

        // Create an instance of StatusController
        $statusController = new StatusController();

        // Call the store method of StatusController
       $statusController->store(new Request([
            'title' => $newLaneName,
            'project_id' => $projectID,
        ]));

        // Return the modified response
        return response()->json(['message' => 'Status created successfully', 'reload' => true]);
    }

    // Update the lane name
    public function updateStatus(Request $request)
    {
        $statusId = $request->input('statusId');
        $newName = $request->input('newName');

        try {
            $status = Status::findOrFail($statusId);
            $status->title = $newName;
            $status->slug = $newName;
            $status->save();

            return response()->json(['message' => 'Lane name updated successfully', 'reload' => true]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error updating lane name'], 500);
        }
    }

    // Update task positions
    public function updateTaskStatus(Request $request)
    {
        $positions = $request->input('positions');

        try {
            foreach ($positions as $position) {
                $task = Task::find($position['taskId']);
                $task->status_id = $position['statusId'];
                $task->order = $position['position'];
                $task->save();
            }

            return response()->json(['message' => 'Task positions saved successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error saving task positions'], 500);
        }
    }

    // Delete a lane
    public function deleteStatus(Request $request)
    {
        $laneId = $request->input('laneId');

        try {
            // Find the order of the lane to be deleted
            $laneOrder = Status::where('id', $laneId)->value('order');

            // Find and delete the tasks associated with the lane using the Task model
            Task::where('status_id', $laneId)->delete();

            // Delete the lane
            Status::destroy($laneId);

            return response()->json(['success' => true, 'message' => 'Lane and associated tasks deleted successfully', 'reload' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Error deleting lane and associated tasks'], 500);
        }
    }   

    
}
