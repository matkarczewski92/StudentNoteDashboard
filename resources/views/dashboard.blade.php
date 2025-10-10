@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Harmonogram</h5>
                    <p class="mb-3">Zestawienie ważnych terminów (np. kolokwii, projektów, sesji) i ogłoszeń (o spotkaniach, imprezach)</p>
                    <a href=" " class="btn btn-primary btn-sm">Przejdź</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Notatki</h5>
                    <p class="mb-3">Notatki z wykładów i ćwiczeń podzielone na semestry, przemioty i daty</p>
                    <a href=" " class="btn btn-primary btn-sm">Przejdź</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Tablica pytań</h5>
                    <p class="mb-3">Ważniejsze pytania w zorganizowanej formie. Z możliwością otwierania i zamykania zrealizowanych</p>
                    <a href=" " class="btn btn-primary btn-sm">Przejdź</a>
                </div>
            </div>
        </div>
        


    </div>
    @can('moderate')
    <hr>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Moderacja</h5>
                    <p class="mb-3"></p>
                    <a href=" " class="btn btn-outline-primary btn-sm">Otwórz</a>
                </div>
            </div>
        </div>
    </div>
    @endcan
    
    @can('admin')
    <hr>
    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm h-100">
                <div class="card-body">
                    <h5 class="card-title">Administracja</h5>
                    <p class="mb-3"></p>
                    <a href=" " class="btn btn-outline-primary btn-sm">Otwórz</a>
                </div>
            </div>
        </div>
    </div>
    @endcan
</div>
@endsection
