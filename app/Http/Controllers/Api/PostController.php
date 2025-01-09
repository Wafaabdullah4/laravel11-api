<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
//import facade Storage
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    // menampilkan seluruh data
    public function index()
    {
        // mengambil semua data post
        $posts = Post::latest()->paginate(5);

        // Kembalikan nilainya ke resource
        return new PostResource(true, 'List Data Post', $posts);
    }

    // Membuat insert pada api
    public function store(Request $request)
    {
        // validasi request yang masuk
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required',
        ]);

        // Jika request tidak sesuai dengan validasi di atas
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // jika sesuai
        // Langkah pertama upload gambar
        $image = $request->file('image');
        $image->storeAs('public/posts', $image->hashName());

        // Melakukan proses insert data ke database
        $post = Post::create([
            'image' => $image->hashName(),
            'title' => $request->title,
            'content' => $request->content,
        ]);

        // jika proses insert selesai kita akan memanggil PostResouce  untuk menampilkan pesan succes
        return new PostResource(true, "Data Berhasil Ditambahkan", $post);
    }

    // Melihat detail
    public function show($id)
    {
        $post = Post::find($id);

        return new PostResource(true, 'Detail Data Post', $post);
    }

    // Melakukan Update
    public function update(Request $request, $id)
    {
        // Validasi request yang masuk
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);

        // kalo validasi nya gagal
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // mengambil data dari model Post dengan method find berdasarkan parameter $id
        $post = Post::find($id);

        // Jika ada perubahan gambar maka lakukan ini
        if ($request->hasFile('image')) {
            // upload ulang gambar
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            // hapus gambar sebelumnya
            Storage::delete(['public/posts', basename($post->image)]);

            // upload posts with new image
            $post->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'content' => $request->content,
            ]);
        } else {
            // Jika tidak ada gambar baru
            $post->update([
                'title' => $request->title,
                'content' => $request->content,
            ]);
        }

        // Mengembalikan nilai respons
        return new PostResource(true, 'Data berhasil di update', $post);
    }

    // Menambahkan function hapus
    public function destroy($id)
    {
        // Cari id
        $post = Post::find($id);

        // Hapus storage
        Storage::delete('public/posts/' . basename($post->image));


        // Hapus post
        $post->delete();

        // Kembalikan response
        return new PostResource(true, "Data Berhasil Dihapus", null);
    }
}
