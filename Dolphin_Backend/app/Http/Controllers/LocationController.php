<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\State;
use App\Models\City;

class LocationController extends Controller

{
    public function countries()
    {
        \Log::info('[LocationController] Fetching countries');
        $countries = Country::orderBy('name')->get(['id', 'name']);
        \Log::info('[LocationController] Countries fetched', ['count' => count($countries)]);
        return response()->json($countries);
    }

    public function states(Request $request)
    {
        $countryId = $request->query('country_id');
        \Log::info('[LocationController] Fetching states for country', ['country_id' => $countryId]);
        $states = State::where('country_id', $countryId)->orderBy('name')->get(['id', 'name']);
        \Log::info('[LocationController] States fetched', ['count' => count($states)]);
        return response()->json($states);
    }

    public function cities(Request $request)
    {
        $stateId = $request->query('state_id');
        \Log::info('[LocationController] Fetching cities for state', ['state_id' => $stateId]);
        $cities = City::where('state_id', $stateId)->orderBy('name')->get(['id', 'name']);
        \Log::info('[LocationController] Cities fetched', ['count' => count($cities)]);
        return response()->json($cities);
    }

        public function getCountryById($id)
    {
        \Log::info('[LocationController] Fetching country by id', ['id' => $id]);
        $country = Country::find($id);
        if ($country) {
            return response()->json(['id' => $country->id, 'name' => $country->name]);
        } else {
            return response()->json(['error' => 'Country not found'], 404);
        }
    }

    public function getStateById($id)
    {
        \Log::info('[LocationController] Fetching state by id', ['id' => $id]);
        $state = State::find($id);
        if ($state) {
            return response()->json(['id' => $state->id, 'name' => $state->name]);
        } else {
            return response()->json(['error' => 'State not found'], 404);
        }
    }

    public function getCityById($id)
    {
        \Log::info('[LocationController] Fetching city by id', ['id' => $id]);
        $city = City::find($id);
        if ($city) {
            return response()->json(['id' => $city->id, 'name' => $city->name]);
        } else {
            return response()->json(['error' => 'City not found'], 404);
        }
    }
}
