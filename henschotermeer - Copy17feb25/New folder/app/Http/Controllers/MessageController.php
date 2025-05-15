<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use App\LocationOptions;
use App\LocationDevices;
use App\AvailableDevices;
use Illuminate\Support\Facades\Session;
use File;

class MessageController extends Controller {

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //
        $message_details = array();
        $msg = array();
        $message_types = \App\MessageType::get();
        if ($message_types->count() > 0) {
            foreach ($message_types as $message_type) {
                $messages = \App\Messages::where('message_type_id', $message_type->id)->get();
                $msg = array(
                    'en' => 'N/A',
                    'nl' => 'N/A',
                    'fr' => 'N/A',
                    'es' => 'N/A',
                    'no' => 'N/A',
                    'gr' => 'N/A',
                    'de' => 'N/A',
                    'fy' => 'N/A',
                    'dr' => 'N/A',
                );
                if ($messages->count() > 0) {
                    foreach ($messages as $message) {
                        if ($message->language_id == 1) {
                            $msg['en'] = $message->message;
                        } elseif ($message->language_id == 2) {
                            $msg['nl'] = $message->message;
                        } elseif ($message->language_id == 3) {
                            $msg['fr'] = $message->message;
                        } elseif ($message->language_id == 4) {
                            $msg['es'] = $message->message;
                        } elseif ($message->language_id == 5) {
                            $msg['no'] = $message->message;
                        } elseif ($message->language_id == 7) {
                            $msg['gr'] = $message->message;
                        } elseif ($message->language_id == 9) {
                            $msg['de'] = $message->message;
                        } elseif ($message->language_id == 6) {
                            $msg['fy'] = $message->message;
                        } elseif ($message->language_id == 8) {
                            $msg['dr'] = $message->message;
                        }
                    }
                }
                $msg['id'] = $message_type->id;
                $message_details[$message_type->name] = (object) $msg;
            }
        }
        return view('messages.index', compact('message_details'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
        $langs = \App\Language::get();
        $message_type = \App\MessageType::get();
        return view('messages.create', compact('langs', 'message_type'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
        try {
            $this->validate($request, [
                'message_key' => 'required',
                'lang_en' => 'required'
            ]);
            $data = $request->all();

            $message_type_id = $data['message_key'];
            if (array_key_exists('lang_en', $data) && !empty($data['lang_en'])) {
                $message = new \App\Messages();
                $message->language_id = 1;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_en'];
                $message->save();
            } else {
                $message = \App\Messages::where([
                            ['language_id', 1],
                            ['message_type_id', $message_type_id],
                        ])->forceDelete();
            }
            if (array_key_exists('lang_nl', $data) && !empty($data['lang_nl'])) {
                $message = new \App\Messages();
                $message->language_id = 2;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_nl'];
                $message->save();
            } else {
                \App\Messages::where([
                    ['language_id', 2],
                    ['message_type_id', $message_type_id],
                ])->forceDelete();
            }
            if (array_key_exists('lang_fr', $data) && !empty($data['lang_fr'])) {
                $message = new \App\Messages();
                $message->language_id = 3;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_fr'];
                $message->save();
            } else {
                $message = \App\Messages::where([
                            ['language_id', 3],
                            ['message_type_id', $message_type_id],
                        ])->forceDelete();
            }
            if (array_key_exists('lang_es', $data) && !empty($data['lang_es'])) {
                $message = new \App\Messages();
                $message->language_id = 4;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_es'];
                $message->save();
            } else {
                $message = \App\Messages::where([
                            ['language_id', 4],
                            ['message_type_id', $message_type_id],
                        ])->forceDelete();
            }
            if (array_key_exists('lang_no', $data) && !empty($data['lang_no'])) {
                $message = new \App\Messages();
                $message->language_id = 5;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_no'];
                $message->save();
            } else {
                $message = \App\Messages::where([
                            ['language_id', 5],
                            ['message_type_id', $message_type_id],
                        ])->forceDelete();
            }
            if (array_key_exists('lang_gr', $data) && !empty($data['lang_gr'])) {
                $message = new \App\Messages();
                $message->language_id = 7;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_gr'];
                $message->save();
            } else {
                $message = \App\Messages::where([
                            ['language_id', 7],
                            ['message_type_id', $message_type_id],
                        ])->forceDelete();
            }
            if (array_key_exists('lang_de', $data) && !empty($data['lang_de'])) {
                $message = new \App\Messages();
                $message->language_id = 9;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_de'];
                $message->save();
            } else {
                $message = \App\Messages::where([
                            ['language_id', 9],
                            ['message_type_id', $message_type_id],
                        ])->forceDelete();
            }
            if (array_key_exists('lang_fy', $data) && !empty($data['lang_fy'])) {
                $message = new \App\Messages();
                $message->language_id = 6;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_fy'];
                $message->save();
            } else {
                $message = \App\Messages::where([
                            ['language_id', 6],
                            ['message_type_id', $message_type_id],
                        ])->forceDelete();
            }
            if (array_key_exists('lang_dr', $data) && !empty($data['lang_dr'])) {
                $message = new \App\Messages();
                $message->language_id = 8;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_dr'];
                $message->save();
            } else {
                $message = \App\Messages::where([
                            ['language_id', 8],
                            ['message_type_id', $message_type_id],
                        ])->forceDelete();
            }

            Session::flash('heading', 'Success!');
            Session::flash('message', __('messages.message_add'));
            Session::flash('icon', 'success');
            return redirect('messages');
        } catch (\Exception $e) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $message_details = array();
        $msg = array();
        $message_type = \App\MessageType::find($id);
        if ($message_type) {
            $messages = \App\Messages::where('message_type_id', $message_type->id)->get();
            $msg = array(
                'en' => '',
                'en_usa' => '',
                'nl' => '',
                'fr' => '',
                'es' => '',
                'no' => '',
                'gr' => '',
                'de' => '',
                'fy' => '',
                'dr' => '',
            );
            if ($messages->count() > 0) {
                foreach ($messages as $message) {
                    if ($message->language_id == 1) {
                        $msg['en'] = $message->message;
                    } elseif ($message->language_id == 2) {
                        $msg['nl'] = $message->message;
                    } elseif ($message->language_id == 3) {
                        $msg['fr'] = $message->message;
                    } elseif ($message->language_id == 4) {
                        $msg['es'] = $message->message;
                    } elseif ($message->language_id == 5) {
                        $msg['no'] = $message->message;
                    } elseif ($message->language_id == 7) {
                        $msg['gr'] = $message->message;
                    } elseif ($message->language_id == 9) {
                        $msg['de'] = $message->message;
                    } elseif ($message->language_id == 6) {
                        $msg['fy'] = $message->message;
                    } elseif ($message->language_id == 8) {
                        $msg['dr'] = $message->message;
                    }
                }
            }
            $msg['id'] = $message_type->id;
            $msg['key'] = $message_type->name;
            $message_details = (object) $msg;
        }

        $langs = \App\Language::get();
        return view('messages.edit', compact('message_details', 'langs'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        //
        try {
            $this->validate($request, [
                'message_key' => 'required',
                'lang_en' => 'required'
            ]);
            $data = $request->all();
            $message_type = \App\MessageType::find($id);

            $message_type_id = $message_type->id;
            if (array_key_exists('lang_en', $data) && !empty($data['lang_en'])) {
                $message = \App\Messages::where([
                            ['language_id', 1],
                            ['message_type_id', $message_type_id],
                        ])->first();
                if (!$message) {
                    $message = new \App\Messages();
                }
                $message->language_id = 1;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_en'];
                $message->save();
            } else {
                \App\Messages::where([
                    ['language_id', 1],
                    ['message_type_id', $message_type_id],
                ])->forceDelete();
            }
            if (array_key_exists('lang_nl', $data) && !empty($data['lang_nl'])) {
                $message = \App\Messages::where([
                            ['language_id', 2],
                            ['message_type_id', $message_type_id],
                        ])->first();
                if (!$message) {
                    $message = new \App\Messages();
                }
                $message->language_id = 2;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_nl'];
                $message->save();
            } else {
                \App\Messages::where([
                    ['language_id', 2],
                    ['message_type_id', $message_type_id],
                ])->forceDelete();
            }
            if (array_key_exists('lang_fr', $data) && !empty($data['lang_fr'])) {
                $message = \App\Messages::where([
                            ['language_id', 3],
                            ['message_type_id', $message_type_id],
                        ])->first();
                if (!$message) {
                    $message = new \App\Messages();
                }
                $message->language_id = 3;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_fr'];
                $message->save();
            } else {
                \App\Messages::where([
                    ['language_id', 3],
                    ['message_type_id', $message_type_id],
                ])->forceDelete();
            }
            if (array_key_exists('lang_es', $data) && !empty($data['lang_es'])) {
                $message = \App\Messages::where([
                            ['language_id', 4],
                            ['message_type_id', $message_type_id],
                        ])->first();
                if (!$message) {
                    $message = new \App\Messages();
                }
                $message->language_id = 4;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_es'];
                $message->save();
            } else {
                \App\Messages::where([
                    ['language_id', 4],
                    ['message_type_id', $message_type_id],
                ])->forceDelete();
            }
            if (array_key_exists('lang_no', $data) && !empty($data['lang_no'])) {
                $message = \App\Messages::where([
                            ['language_id', 5],
                            ['message_type_id', $message_type_id],
                        ])->first();
                if (!$message) {
                    $message = new \App\Messages();
                }
                $message->language_id = 5;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_no'];
                $message->save();
            } else {
                \App\Messages::where([
                    ['language_id', 5],
                    ['message_type_id', $message_type_id],
                ])->forceDelete();
            }
            if (array_key_exists('lang_gr', $data) && !empty($data['lang_gr'])) {
                $message = \App\Messages::where([
                            ['language_id', 7],
                            ['message_type_id', $message_type_id],
                        ])->first();
                if (!$message) {
                    $message = new \App\Messages();
                }
                $message->language_id = 7;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_gr'];
                $message->save();
            } else {
                \App\Messages::where([
                    ['language_id', 7],
                    ['message_type_id', $message_type_id],
                ])->forceDelete();
            }
            if (array_key_exists('lang_de', $data) && !empty($data['lang_de'])) {
                $message = \App\Messages::where([
                            ['language_id', 9],
                            ['message_type_id', $message_type_id],
                        ])->first();
                if (!$message) {
                    $message = new \App\Messages();
                }
                $message->language_id = 9;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_de'];
                $message->save();
            } else {
                \App\Messages::where([
                    ['language_id', 9],
                    ['message_type_id', $message_type_id],
                ])->forceDelete();
            }
            if (array_key_exists('lang_fy', $data) && !empty($data['lang_fy'])) {
                $message = \App\Messages::where([
                            ['language_id', 6],
                            ['message_type_id', $message_type_id],
                        ])->first();
                if (!$message) {
                    $message = new \App\Messages();
                }
                $message->language_id = 6;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_fy'];
                $message->save();
            } else {
                \App\Messages::where([
                    ['language_id', 6],
                    ['message_type_id', $message_type_id],
                ])->forceDelete();
            }
            if (array_key_exists('lang_dr', $data) && !empty($data['lang_dr'])) {
                $message = \App\Messages::where([
                            ['language_id', 8],
                            ['message_type_id', $message_type_id],
                        ])->first();
                if (!$message) {
                    $message = new \App\Messages();
                }
                $message->language_id = 8;
                $message->message_type_id = $message_type_id;
                $message->message = $data['lang_dr'];
                $message->save();
            } else {
                \App\Messages::where([
                    ['language_id', 8],
                    ['message_type_id', $message_type_id],
                ])->forceDelete();
            }

            Session::flash('heading', 'Success!');
            Session::flash('message', __('messages.message_update'));
            Session::flash('icon', 'success');
            return redirect('messages');
        } catch (\Exception $e) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        try {
            $message_type = \App\MessageType::find($id);
            if ($message_type) {
                $message_type_id = $message_type->id;
                \App\Messages::where('message_type_id', $message_type_id)->forceDelete();
                Session::flash('heading', 'Success!');
                Session::flash('message', 'Message has been deleted.');
                Session::flash('icon', 'success');
                return redirect('messages');
            } else {
                Session::flash('heading', 'Error!');
                Session::flash('message', 'Message not found');
                Session::flash('icon', 'error');
                return redirect()->back();
            }
        } catch (\Exception $e) {
            Session::flash('heading', 'Error!');
            Session::flash('message', $e->getMessage());
            Session::flash('icon', 'error');
            return redirect()->back();
        }
    }

}
