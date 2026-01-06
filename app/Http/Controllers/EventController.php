<?php

namespace App\Http\Controllers;

use Log;
use Auth;
use Image;
use Validator;
use App\Models\Event;
use App\Models\Organiser;
use App\Models\EventImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\GoogleCalendar\Event as GCEvent;
use Carbon\Carbon;

class EventController extends MyBaseController
{
    /**
     * Show the 'Create Event' Modal
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showCreateEvent(Request $request)
    {
        $data = [
            'modal_id'     => $request->get('modal_id'),
            'organisers'   => Organiser::scope()->pluck('name', 'id'),
            'organiser_id' => $request->get('organiser_id') ? $request->get('organiser_id') : false,
        ];

        return view('ManageOrganiser.Modals.CreateEvent', $data);
    }

    /**
     * Create an event
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
  public function postCreateEvent(Request $request)
{
    $event = Event::createNew();

    // PROCESS DATES BEFORE VALIDATION
    $processedRequest = $this->preprocessDates($request);

    if (!$event->validate($processedRequest->all())) {
        return response()->json([
            'status'   => 'error',
            'messages' => $event->errors(),
        ]);
    }

    $event->title = $processedRequest->get('title');
    $event->description = prepare_markdown($processedRequest->get('description'));
    $event->start_date = $processedRequest->get('start_date');

    /*
     * Venue location info (Usually auto-filled from google maps)
     */

    $is_auto_address = (trim($processedRequest->get('place_id')) !== '');

    if ($is_auto_address) { /* Google auto filled */
        $event->venue_name = $processedRequest->get('name');
        $event->venue_name_full = $processedRequest->get('venue_name_full');
        $event->location_lat = $processedRequest->get('lat');
        $event->location_long = $processedRequest->get('lng');
        $event->location_address = $processedRequest->get('formatted_address');
        $event->location_country = $processedRequest->get('country');
        $event->location_country_code = $processedRequest->get('country_short');
        $event->location_state = $processedRequest->get('administrative_area_level_1');
        $event->location_address_line_1 = $processedRequest->get('route');
        $event->location_address_line_2 = $processedRequest->get('locality');
        $event->location_post_code = $processedRequest->get('postal_code');
        $event->location_street_number = $processedRequest->get('street_number');
        $event->location_google_place_id = $processedRequest->get('place_id');
        $event->location_is_manual = 0;
    } else { /* Manually entered */
        $event->venue_name = $processedRequest->get('location_venue_name');
        $event->location_address_line_1 = $processedRequest->get('location_address_line_1');
        $event->location_address_line_2 = $processedRequest->get('location_address_line_2');
        $event->location_state = $processedRequest->get('location_state');
        $event->location_post_code = $processedRequest->get('location_post_code');
        $event->location_is_manual = 1;
    }

    $event->end_date = $processedRequest->get('end_date');

    $event->currency_id = Auth::user()->account->currency_id;
    
    /*
     * Set a default background for the event
     */
    $event->bg_type = 'image';
    $event->bg_image_path = config('attendize.event_default_bg_image');

    if ($processedRequest->get('organiser_name')) {
        $organiser = Organiser::createNew(false, false, true);

        $rules = [
            'organiser_name'  => ['required'],
            'organiser_email' => ['required', 'email'],
        ];
        $messages = [
            'organiser_name.required' => trans("Controllers.no_organiser_name_error"),
        ];

        $validator = Validator::make($processedRequest->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status'   => 'error',
                'messages' => $validator->messages()->toArray(),
            ]);
        }

        $organiser->name = $processedRequest->get('organiser_name');
        $organiser->about = prepare_markdown($processedRequest->get('organiser_about'));
        $organiser->email = $processedRequest->get('organiser_email');
        $organiser->facebook = $processedRequest->get('organiser_facebook');
        $organiser->twitter = $processedRequest->get('organiser_twitter');
        $organiser->save();
        $event->organiser_id = $organiser->id;
    } elseif ($processedRequest->get('organiser_id')) {
        $event->organiser_id = $processedRequest->get('organiser_id');
    } else { /* Somethings gone horribly wrong */
        return response()->json([
            'status'   => 'error',
            'messages' => trans("Controllers.organiser_other_error"),
        ]);
    }

    /*
     * Set the event defaults.
     */
    $defaults = $event->organiser->event_defaults;
    if ($defaults) {
        $event->organiser_fee_fixed = $defaults->organiser_fee_fixed;
        $event->organiser_fee_percentage = $defaults->organiser_fee_percentage;
        $event->pre_order_display_message = $defaults->pre_order_display_message;
        $event->post_order_display_message = $defaults->post_order_display_message;
        $event->offline_payment_instructions = prepare_markdown($defaults->offline_payment_instructions);
        $event->enable_offline_payments = $defaults->enable_offline_payments;
        $event->social_show_facebook = $defaults->social_show_facebook;
        $event->social_show_linkedin = $defaults->social_show_linkedin;
        $event->social_show_twitter = $defaults->social_show_twitter;
        $event->social_show_email = $defaults->social_show_email;
        $event->social_show_whatsapp = $defaults->social_show_whatsapp;
        $event->is_1d_barcode_enabled = $defaults->is_1d_barcode_enabled;
        $event->ticket_border_color = $defaults->ticket_border_color;
        $event->ticket_bg_color = $defaults->ticket_bg_color;
        $event->ticket_text_color = $defaults->ticket_text_color;
        $event->ticket_sub_text_color = $defaults->ticket_sub_text_color;
    }

    try {
        $event->save();
    } catch (\Exception $e) {
        Log::error($e);

        return response()->json([
            'status'   => 'error',
            'messages' => trans("Controllers.event_create_exception"),
        ]);
    }

    // ✅ FIXED: Use storage instead of public directory
    if ($processedRequest->hasFile('event_image')) {
        $filename = 'event_image-' . md5(time() . $event->id) . '.' . strtolower($processedRequest->file('event_image')->getClientOriginalExtension());
        
        // Store in storage/app/public/event_images
        $storagePath = 'event_images/' . $filename;
        $processedRequest->file('event_image')->storeAs('event_images', $filename, 'public');
        
        // Get full path for image manipulation
        $file_full_path = storage_path('app/public/' . $storagePath);

        try {
            $img = Image::make($file_full_path);
            $img->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save($file_full_path);
        } catch (\Exception $e) {
            Log::warning('Image resize failed: ' . $e->getMessage());
        }

        // Also upload to configured storage (S3, etc.) if needed
        if (config('filesystems.default') !== 'public') {
            Storage::put(config('attendize.event_images_path') . '/' . $filename, file_get_contents($file_full_path));
        }

        $eventImage = EventImage::createNew();
        $eventImage->image_path = $storagePath;
        $eventImage->event_id = $event->id;
        $eventImage->save();
    }

    return response()->json([
        'status'      => 'success',
        'id'          => $event->id,
        'redirectUrl' => route('showEventTickets', [
            'event_id'  => $event->id,
            'first_run' => 'yup',
        ]),
    ]);
}

/**
 * Preprocess the request to format dates correctly before validation
 *
 * @param Request $request
 * @return Request
 */
private function preprocessDates(Request $request)
{
    $data = $request->all();
    
    // Process start_date
    if (isset($data['start_date']) && !empty($data['start_date'])) {
        $data['start_date'] = $this->formatDateForValidation($data['start_date']);
        Log::info('Preprocessed start_date: ' . $data['start_date']);
    }
    
    // Process end_date
    if (isset($data['end_date']) && !empty($data['end_date'])) {
        $data['end_date'] = $this->formatDateForValidation($data['end_date']);
        Log::info('Preprocessed end_date: ' . $data['end_date']);
    }
    
    // Create new request with processed data
    $newRequest = new Request($data);
    
    // Copy files if they exist
    $newRequest->files = $request->files;
    
    return $newRequest;
}

/**
 * Format date to match the config format expected by model validation
 *
 * @param string $dateString
 * @return string
 */
private function formatDateForValidation($dateString)
{
    if (empty($dateString)) {
        return '';
    }
    
    try {
        // Get the format that the Event model expects
        $expectedFormat = config('attendize.default_datetime_format', 'Y-m-d H:i:s');
        
        Log::info('Expected date format from config: ' . $expectedFormat);
        Log::info('Input date string: ' . $dateString);
        
        // Handle HTML5 datetime-local format (2024-01-15T14:30)
        if (strpos($dateString, 'T') !== false) {
            $dateString = str_replace('T', ' ', $dateString);
        }
        
        // Parse using Carbon and format to the expected format
        $carbon = Carbon::parse($dateString);
        $formattedDate = $carbon->format($expectedFormat);
        
        Log::info('Formatted date: ' . $formattedDate);
        
        return $formattedDate;
        
    } catch (\Exception $e) {
        Log::error('Date formatting error: ' . $e->getMessage() . ' for input: ' . $dateString);
        // Return original if parsing fails - let model validation handle the error
        return $dateString;
    }
}

    /**
     * Edit an event
     *
     * @param Request $request
     * @param $event_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function postEditEvent(Request $request, $event_id)
    {
        $event = Event::scope()->findOrFail($event_id);

            // DEBUG: Log file info
    if ($request->hasFile('event_image')) {
        \Log::info('File upload attempt:', [
            'name' => $request->file('event_image')->getClientOriginalName(),
            'mime' => $request->file('event_image')->getMimeType(),
            'size' => $request->file('event_image')->getSize(),
            'extension' => $request->file('event_image')->getClientOriginalExtension(),
            'is_valid' => $request->file('event_image')->isValid(),
        ]);
    }

        if (!$event->validate($request->all())) {
            return response()->json([
                'status'   => 'error',
                'messages' => $event->errors(),
            ]);
        }

        $event->is_live = $request->get('is_live');
        $event->currency_id = $request->get('currency_id');
        $event->title = $request->get('title');
        $event->description = prepare_markdown($request->get('description'));
        $event->start_date = $request->get('start_date');
        $event->google_tag_manager_code = $request->get('google_tag_manager_code');

        /*
         * If the google place ID is the same as before then don't update the venue
         */
        if (($request->get('place_id') !== $event->location_google_place_id) || $event->location_google_place_id == '') {
            $is_auto_address = (trim($request->get('place_id')) !== '');

            if ($is_auto_address) { /* Google auto filled */
                $event->venue_name = $request->get('name');
                $event->venue_name_full = $request->get('venue_name_full');
                $event->location_lat = $request->get('lat');
                $event->location_long = $request->get('lng');
                $event->location_address = $request->get('formatted_address');
                $event->location_country = $request->get('country');
                $event->location_country_code = $request->get('country_short');
                $event->location_state = $request->get('administrative_area_level_1');
                $event->location_address_line_1 = $request->get('route');
                $event->location_address_line_2 = $request->get('locality');
                $event->location_post_code = $request->get('postal_code');
                $event->location_street_number = $request->get('street_number');
                $event->location_google_place_id = $request->get('place_id');
                $event->location_is_manual = 0;
            } else { /* Manually entered */
                $event->venue_name = $request->get('location_venue_name');
                $event->location_address_line_1 = $request->get('location_address_line_1');
                $event->location_address_line_2 = $request->get('location_address_line_2');
                $event->location_state = $request->get('location_state');
                $event->location_post_code = $request->get('location_post_code');
                $event->location_is_manual = 1;
                $event->location_google_place_id = '';
                $event->venue_name_full = '';
                $event->location_lat = '';
                $event->location_long = '';
                $event->location_address = '';
                $event->location_country = '';
                $event->location_country_code = '';
                $event->location_street_number = '';
            }
        }

        $event->end_date = $request->get('end_date');
        $event->event_image_position = $request->get('event_image_position');

        if ($request->get('remove_current_image') == '1') {
            EventImage::where('event_id', '=', $event->id)->delete();
        }

        $event->save();

        // ✅ FIXED: Use storage instead of public directory
        if ($request->hasFile('event_image')) {
            $filename = 'event_image-' . md5(time() . $event->id) . '.' . strtolower($request->file('event_image')->getClientOriginalExtension());
            
            // Store in storage/app/public/event_images
            $storagePath = 'event_images/' . $filename;
            $request->file('event_image')->storeAs('event_images', $filename, 'public');
            
            // Get full path for image manipulation
            $file_full_path = storage_path('app/public/' . $storagePath);

            try {
                $img = Image::make($file_full_path);
                $img->resize(800, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $img->save($file_full_path);
            } catch (\Exception $e) {
                Log::warning('Image resize failed: ' . $e->getMessage());
            }

            // Also upload to configured storage (S3, etc.) if needed
            if (config('filesystems.default') !== 'public') {
                Storage::put(config('attendize.event_images_path') . '/' . $filename, file_get_contents($file_full_path));
            }

            EventImage::where('event_id', '=', $event->id)->delete();

            $eventImage = EventImage::createNew();
            $eventImage->image_path = $storagePath;
            $eventImage->event_id = $event->id;
            $eventImage->save();
        }

        return response()->json([
            'status'      => 'success',
            'id'          => $event->id,
            'message'     => trans("Controllers.event_successfully_updated"),
            'redirectUrl' => '',
        ]);
    }

    /**
     * Upload event image
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function postUploadEventImage(Request $request)
    {
        // ✅ FIXED: Use storage instead of public directory
        if ($request->hasFile('event_image')) {
            $file_name = 'event_details_image-' . md5(microtime()) . '.' . strtolower($request->file('event_image')->getClientOriginalExtension());
            
            // Store in storage/app/public/event_images
            $storagePath = 'event_images/' . $file_name;
            $request->file('event_image')->storeAs('event_images', $file_name, 'public');
            
            // Get full path for image manipulation
            $full_path_to_file = storage_path('app/public/' . $storagePath);

            try {
                $img = Image::make($full_path_to_file);
                $img->resize(1000, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
                $img->save($full_path_to_file);
            } catch (\Exception $e) {
                Log::warning('Image resize failed: ' . $e->getMessage());
            }

            // Also upload to configured storage (S3, etc.) if needed
            if (config('filesystems.default') !== 'public') {
                $the_file = \File::get($request->file('event_image')->getRealPath());
                Storage::put($file_name, $the_file);
            }

            return response()->json([
                'link' => Storage::disk('public')->url($storagePath),
            ]);
        }

        return response()->json([
            'error' => trans("Controllers.image_upload_error"),
        ]);
    }

    /**
     * Puplish event and redirect
     * @param  Integer|false $event_id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postMakeEventLive($event_id = false) {
        $event = Event::scope()->findOrFail($event_id);
        $event->is_live = 1;
        $event->save();
        \Session::flash('message', trans('Event.go_live'));

        return redirect()->action(
            'EventDashboardController@showDashboard', ['event_id' => $event_id]
        );
    }
}