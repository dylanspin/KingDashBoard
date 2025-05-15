<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use App\UnwantedCharacter;
use App\LocationOptions;
use Session;

class UnwantedCharacterController extends Controller
{
    //
    public function index()
    {
        $location = LocationOptions::first();
        $unwanted_characters = UnwantedCharacter::orderBy('created_at', 'DESC')->get();
        return view('unwanted_character.index', [
            'location' => $location,
            'unwanted_characters' => $unwanted_characters
        ]);
    }
    public function create()
    {
        $location = LocationOptions::first();
        return view('unwanted_character.create', [
            'location' => $location
        ]);
    }
    public function store(Request $request)
    {
        // try {
        $data = $request->all();
        $unwanted_character = new UnwantedCharacter();
        $unwanted_character->unwanted_character = $data['unwanted_character'];
        $unwanted_character->valid_character = $data['valid_character'];
        if ($unwanted_character->exists()) {
            $unwanted_character->update();
        }
        $unwanted_character->save();
        if ($unwanted_character) {
            Session::flash('heading', 'Success!');
            Session::flash('message', __('unwanted-character.add_message'));
            Session::flash('icon', 'success');
            return redirect('unwanted-character');
        }
        // } catch (Exception $ex) {
        //     Session::flash('heading', 'Error!');
        //     Session::flash('message', $ex->getMessage());
        //     Session::flash('icon', 'error');
        //     return redirect()->back()->withInput();;
        // }
    }
    public function edit($id)
    {
        $location = LocationOptions::first();
        $unwanted_character = UnwantedCharacter::where('id', $id)->first();
        return view('unwanted_character.edit', [
            'location' => $location,
            'unwanted_character' => $unwanted_character
        ]);
    }
    public function update(Request $request)
    {
        try {
            $data = $request->all();
            $unwanted_character = UnwantedCharacter::where('id', $data['character_id'])->first();
            if (!$unwanted_character) {
                $unwanted_character = new UnwantedCharacter();
            }
            $unwanted_character->unwanted_character = $data['unwanted_character'];
            $unwanted_character->valid_character = $data['valid_character'];
            if ($unwanted_character->exists()) {
                $unwanted_character->update();
            }
            $unwanted_character->save();
            if ($unwanted_character) {
                Session::flash('heading', 'Success!');
                Session::flash('message', __('unwanted-character.update_message'));
                Session::flash('icon', 'success');
                return redirect('unwanted-character');
            }
        } catch (Exception $ex) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $ex->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();;
        }
    }
    public function delete($id)
    {
        $unwanted_character = UnwantedCharacter::where('id', $id)->first();
        if ($unwanted_character) {
            $unwanted_character->delete();
            Session::flash('heading', 'Success!');
            Session::flash('message', __('access_rules.delete_message'));
            Session::flash('icon', 'success');
            return redirect('unwanted-character');
        }
    }
}
