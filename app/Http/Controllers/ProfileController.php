<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UploadDocumentsRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        return view('profile.show', ['user' => $request->user()]);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $this->replace(
                $request->file('avatar'),
                $user->avatar_path,
                config('aparcado.uploads.avatar_disk'),
                'avatars',
            );
        }

        unset($data['avatar']);

        $user->update($data);

        return back()->with('status', 'Guardado.');
    }

    public function documents(UploadDocumentsRequest $request): RedirectResponse
    {
        $user = $request->user();
        $disk = config('aparcado.uploads.documents_disk');

        $user->update([
            'document_photo_path' => $this->replace($request->file('document_photo'), $user->document_photo_path, $disk, 'documents'),
            'licence_photo_path' => $this->replace($request->file('licence_photo'), $user->licence_photo_path, $disk, 'licences'),
        ]);

        return back()->with('status', 'Papeles recibidos. Te avisamos en cuanto los miremos.');
    }

    public function password(UpdatePasswordRequest $request): RedirectResponse
    {
        // El cast `hashed` del modelo la cifra; aquí nunca se ve un `bcrypt()`.
        $request->user()->update(['password' => $request->validated('password')]);

        return back()->with('status', 'Contraseña cambiada.');
    }

    /**
     * Guarda el fichero nuevo y borra el que había.
     *
     * Borrar el viejo no es limpieza: una foto de un DNI que ya no se usa sigue
     * siendo la foto de un DNI, y cuantas menos copias haya, mejor.
     */
    private function replace(UploadedFile $file, ?string $previous, string $disk, string $folder): string
    {
        $path = $file->store($folder, $disk);

        if ($previous !== null && $previous !== $path) {
            Storage::disk($disk)->delete($previous);
        }

        return $path;
    }
}
