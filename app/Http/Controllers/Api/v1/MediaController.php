<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'media' => 'required|file|mimes:' . config('misc.media.mimes') . '|max:' . config('misc.media.maxsize')
        ]);

        $user = auth()->user();

        // video
        $file = $request->file('media');
        $mime = $file->getMimeType();

        $type = null;

        if (strstr($mime, 'video') !== false) {
            $type = Media::TYPE_VIDEO;
        } else if (strstr($mime, 'audio') !== false) {
            $type = Media::TYPE_AUDIO;
        } else if (strstr($mime, 'image') !== false) {
            $type = Media::TYPE_IMAGE;
        }

        if ($type != null) {
            $media = $user->media()->create([
                'type' => $type,
                'extension' => $file->extension()
            ]);
            $file->storeAs('tmp', $media->hash . '.' . $file->extension());
        }

        return response()->json($media);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Media  $media
     * @return \Illuminate\Http\Response
     */
    public function destroy(Media $media)
    {
        $media->delete();
        return response()->json(['status' => true]);
    }
}