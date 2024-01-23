<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Traits\CanLoadRelationships;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EventController extends Controller
{
    use CanLoadRelationships;

    private array $relations = ['user', 'attendees', 'attendees.user'];

    public function __construct()
    {
        //this protect every methods with middleware and connect to routes
        $this->middleware('auth:sanctum')->except(['index', 'show']);
        $this->authorizeResource(Event::class, 'event');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = $this->loadRelationships(Event::query());

        // this is using API Resource for transform/custom data 
        return EventResource::collection($query->latest()->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $event = Event::create([
            //this is using spread operator for remove array in request
            ...$request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'required|date',
                'end_time' => 'required|date|after:start_time' //this value after start_time
            ]),
            'user_id' => $request->user()->id
        ]);

        // this is using API Resource for transform/custom data 
        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event)
    {
        // $event->load('user', 'attendees');
        // this is using API Resource for transform/custom data 
        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Event $event)
    {
        //this is will check if fail authorization
        // if (Gate::denies('update-event', $event)) {
        //     abort(403, 'You are not authorized to update this event.');
        // }

        //this is same like above but you need define in Gate at AuthServiceProvider
        // Gate::authorize('update-event', $event);

        $event->update(
            $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'sometimes|date',
                'end_time' => 'sometimes|date|after:start_time' //this value after start_time
            ])
        );

        // this is using API Resource for transform/custom data 
        return new EventResource($this->loadRelationships($event));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event)
    {
        $event->delete();

        //this for no content 
        // return response(status: 204)

        return response()->json([
            'message' => 'Event deleted successfully'
        ]);
    }
}
