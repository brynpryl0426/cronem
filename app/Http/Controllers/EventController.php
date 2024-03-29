<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $events = Event::all();
        return view("admin.event_modules.index", compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $status = EventStatus::orderBy('stat_id','asc')->pluck('status', 'stat_id');
        return view("admin.event_modules.create")->with('status',$status);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:100',
            'theme' => 'nullable',
            'reg_start' => 'required',
            'event_start' => 'required',
            'event_end' => 'required',
            'event_fee' => 'required',
            'route_map' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'event_status' => 'required'
        ]);

        if ($image = $request->file('route_map')) {
            $destinationPath = 'appimages/events/';
            $routeMapImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move($destinationPath, $routeMapImage);

            $event = new Event;
            $event -> title  = $request-> title;
            $event -> theme  = $request-> theme;
            $event -> reg_start  = $request-> reg_start;
            $event -> event_start  = $request-> event_start;
            $event -> event_end  = $request-> event_end;
            $event -> event_fee  = $request-> event_fee;
            $event -> route_map  = $routeMapImage ;
            $event -> event_status  = $request-> event_status;
            $event->save();
        }


        return redirect()->route('createEvent')->with('success','Event created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Event $event)
    {
        return view('admin.event_modules.show',compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Event $event)
    {
        $status = EventStatus::orderBy('stat_id','asc')->pluck('status', 'stat_id');
        return view('admin.event_modules.edit',compact('event'))->with('status',$status);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Event $event)
    {
        $request->validate([
            'title' => 'required|max:100',
            'theme' => 'nullable',
            'event_start' => 'required',
            'event_end' => 'required',
            'event_fee' => 'required',
            'route_map' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'event_status' => 'required' 
        ]);
       
        $input = $request->all();
        if ($image = $request->file('route_map')) {
            $destinationPath = 'appimages/events/';
            
            if (File::exists(public_path($destinationPath.($event->route_map)))) {
                File::delete(public_path($destinationPath.($event->route_map)));
            }
            
            $routeMapImage = date('YmdHis') . "." . $image->getClientOriginalExtension();
            $image->move($destinationPath, $routeMapImage);
            $event -> route_map = "$routeMapImage";
            
        }else{
            unset($event -> route_map);
        }
        $event->update($input);

        return redirect()->route('eventsList')->with('success','Event updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Event $event)
    {
        $destinationPath = 'appimages/events/';
        if (File::exists(public_path($destinationPath.($event->route_map)))) {
            File::delete(public_path($destinationPath.($event->route_map)));
        }
        $event->delete();

        return redirect()->route('eventsList')
                       ->with('success','Event deleted successfully.');
    }
}
