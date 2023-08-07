<?php

namespace Awcodes\Curator\Http\Controllers;

use Awcodes\Curator\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use League\Glide\Filesystem\FileNotFoundException;
use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;

class MediaController extends Controller
{
    public function show(Request $request, $path)
    {
        try {
            SignatureFactory::create(config('app.key'))->validateRequest('/curator/' . $path, $request->all());
        } catch (SignatureException $e) {
            abort(403);
        } catch (FileNotFoundException $e) {
            abort(404);
        }

        $media = Media::query()->where('path', $path)->first();

        if ($media && ! $media->resizable) {
            return Storage::disk($media->disk)->response($media->path);
        }

        $server = app(config('curator.glide.server'))->getFactory();
        $server->setBaseUrl('/curator/');

        return $server->getImageResponse($path, request()->all());
    }
}
