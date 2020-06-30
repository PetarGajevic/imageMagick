@include('layouts.app')

<form action="/work" method="post" enctype="multipart/form-data">
    @csrf
    <label>Select image to upload:</label>
    <input type="file" name="file" id="file">
    <input type="submit" value="Upload" name="submit">

</form>

<form action="/work/mockup" method="post" enctype="multipart/form-data">
    @csrf
    <label>Select image to upload:</label>
    <input type="file" name="file" id="file">
    <input type="submit" value="Upload" name="submit">

</form>

<form action="/work/mockup1" method="post" enctype="multipart/form-data">
    @csrf
    <label>Select image to upload:</label>
    <input type="file" name="file" id="file">
    <input type="submit" value="Upload" name="submit">

</form>

<form action="/work/mockup2" method="post" enctype="multipart/form-data">
    @csrf
    <label>Select image to upload:</label>
    <input type="file" name="file" id="file">
    <input type="submit" value="Upload" name="submit">

</form>

<form action="/work/mockup3" method="post" enctype="multipart/form-data">
    @csrf
    <label>Select image to upload:</label>
    <input type="file" name="file" id="file">
    <input type="submit" value="Upload" name="submit">

</form>

<form action="/work/mockup4" method="post" enctype="multipart/form-data">
    @csrf
    <label>Select image to upload:</label>
    <input type="file" name="file" id="file">
    <input type="submit" value="Upload" name="submit">

</form>

<form action="/work/mockup5" method="post" enctype="multipart/form-data">
    @csrf
    <label>Select image to upload:</label>
    <input type="file" name="file" id="file">
    <input type="submit" value="Upload" name="submit">

</form>