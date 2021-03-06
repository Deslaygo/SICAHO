<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\NuevoProfesorRequest;
use Illuminate\Support\Facades\Input;
use Validator;
use Response;
use App\Http\Requests;
use Illuminate\Database\QueryException; 
//Uso de los modelos 
use App\Profesor;
use App\Carga_horaria;
use App\Especialidad;
use App\Programa_educativo;
use App\Actividad_extra;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\LoginRequest;
class ProfesorController extends Controller
{
     //se crea el index para post
    public function index(){
     //obtiene una lista de los programas educativos 
        $programasEducativos=Programa_educativo::orderBy('nombreProgramaEducativo','asc')->pluck('nombreProgramaEducativo','id');
        //regresa una indice de todos los profesores junto con una paginación 
      $profesores = Profesor::paginate(15);
        //retorna la vista y pasa la variable profesores a esa vista
      return view('modulos.profesores.main',[
          'profesores'=>$profesores,
          'programasEducativos'=>$programasEducativos,
      ]);
    }
    //Método para agregar a un profesor a la base de datos 
    public function nuevoProfesor(Request $request){
        //try que realiza todo el código de guardado
        try{
        //crea una instancia del modelo para poder acceder a la base de datos 
        $profesor= new Profesor;
        //recibe los request ubicados en el formulario 
        $profesor->clave=$request->input('clave');
        $profesor->nombre=$request->input('nombre');
        $profesor->apellidoPaterno=$request->input('apellidoPaterno');
        $profesor->apellidoMaterno=$request->input('apellidoMaterno');
        $profesor->tipoProfesor=$request->get('tipoProfesor');
        $profesor->id_programa_educativo=$request->get('programaEducativo');    
        $profesor->save();
        
        return back()->with('success','Los datos han sido guardados correctamente');
        }catch(QueryException $ex){
            return redirect('/profesores')->with('status','Algunos datos no se han ingresado correctamente por favor intente de nuevo');
        }      
    }
    
    //método para eliminar a un profesor
    public function eliminarProfesor(Request $request){
        $id_profesor =$request->input('id');
        //Busca la carga horaria del profesor por medio de su id
        $cargaHoraria=Carga_horaria::where('id_profesor',$id_profesor);
        //encuentra al profesor por medio de su id
        $profesor = Profesor::findOrFail($id_profesor);
        //regresa true si el objeto $cargaHoraria es null
        if(is_null($cargaHoraria)){
        $profesor->delete();
             //regre a la vista con un mensaje de exito
      return back()->with('success','Los datos han sido eliminados correctamente');
        }else{
            $profesor->delete();
            $cargaHoraria->delete();
             //regre a la vista con un mensaje de exito
      return back()->with('success','Los datos han sido eliminados correctamente');
        }
    }
    //método para actualizar a un profesor
    public function actualizarProfesor(Request $request){
          try{
            $id =$request->input('id');
            $profesor = Profesor::find($id);      
            $profesor->clave = $request->input('clave');
            $profesor->nombre  = $request->input('nombre');
            $profesor->apellidoPaterno = $request->input('apellidoPaterno');
            $profesor->apellidoMaterno  = $request->input('apellidoMaterno');
            $profesor->tipoProfesor  = $request->get('tipoProfesor');
            $profesor->save();
            return back()->with('success','Los datos han sido actualizados correctamente');
            }catch(QueryException $ex){
                return redirect('/profesores')->with('status','Algunos datos no se han ingresado correctamente por favor intente de nuevo');
            }
    }
    //método para mostrar a un profesor
    public function showPerfil(Profesor $profesor){
       //obtiene una lista que es pasada al select de programas educativos
        $programasEducativos=Programa_educativo::orderBy('nombreProgramaEducativo','asc')->pluck('nombreProgramaEducativo','id');
        //obtiene una lista pasada al select de actividades
       $actividades = Actividad_extra::orderBy('nombre','asc')->pluck('nombre','id');
        //obtiene el id traido de la tabla
        $idProfesor = $profesor->id;
        //busca si existe una carga horaria con el id del profesor y la asigna a la variable $cargaHoraria
        $cargaHoraria= Carga_horaria::where('id_profesor',$idProfesor)->first();
       
        
        
        //regresa true si el objeto $cargaHoraria es null
        if(is_null($cargaHoraria)){
            //en caso de que retorne true solo mandara la variable profesor
            return view('modulos.profesores.perfil',[
            'profesor' => $profesor,
            'programasEducativos'=> $programasEducativos,
            'actividades' => $actividades,
        ]);
        //de lo contrario pasara los objetos cargaHoraria y especialidad     
        }else{
             $id_carga_horaria=$cargaHoraria->id;
            //obtiene todas las asignaturas relacionadas al profesor
            $asignacionAsignaturas=$this->getAsignaturasProfesor($id_carga_horaria);
            //obtiene todas las actividades relacionadas al profesor
            $actividadesProfesor=$this->getActividadesProfesor($id_carga_horaria);
            //retorna la vista y pasa todas las variables
            return view('modulos.profesores.perfil',[
            'profesor' => $profesor,
            'programasEducativos'=> $programasEducativos,
            'cargaHoraria' => $cargaHoraria,
            'asignacionAsignaturas'=>$asignacionAsignaturas,
            'actividades' => $actividades,
            'actividadesProfesor' => $actividadesProfesor,
        ]);
        
        }
    }

    //método que devuelve todad las asignaturas relacionadas con una carga horaria
    public function getAsignaturasProfesor($id_carga_horaria){
        //encuentra la carga horaria por medio del parametro pasado
        $cargaHoraria=Carga_horaria::findOrFail($id_carga_horaria);
        //asigna toda la collección de asignacion al objeto asignaturasProfesor
        $asignaturasProfesor=$cargaHoraria->asignacionAsignaturas()->get();
        //retorna la collección de datos
        return $asignaturasProfesor;
    }

    //método que devuelve todad las actividades relacionadas con una carga horaria
    public function getActividadesProfesor($id_carga_horaria){
        //encuentra la carga horaria por medio del parametro pasado
        $cargaHoraria=Carga_horaria::findOrFail($id_carga_horaria);
        //asigna toda la collección de asignacion al objeto asignaturasProfesor
        $actividadesProfesor=$cargaHoraria->actividadesExtra()->get();
        //retorna la collección de datos
        return $actividadesProfesor;
    }
    


    public function indexD(){

      $carrera=Auth::user()->estado;
      //obtiene una lista de los programas educativos 
        $programasEducativos=Programa_educativo::where('nombreProgramaEducativo',$carrera)->orderBy('nombreProgramaEducativo','asc')->pluck('nombreProgramaEducativo','id');
        //dd($programasEducativos);
        switch ($carrera) {
            case 'Tecnologías de la Información y Comunicación':
                $tics=Programa_educativo::where('nombreProgramaEducativo','Tecnologías de la Información y Comunicación')->first();
                $idTics=$tics->id;
                $proEdu=Profesor::where('id_programa_educativo',$idTics)->get();
                $profesor= $proEdu;
                //dd($profesor);
                break;
            case 'Agricultura Sustentable y Protegida':
                $agricultura=Programa_educativo::where('nombreProgramaEducativo','Agricultura Sustentable y Protegida')->first();
                $idAgri=$agricultura->id;
                $proEdu=Profesor::where('id_programa_educativo',$idAgri)->get();
                $profesor= $proEdu;
                break;
            case 'Gestión de Proyectos':
                $gestion=Programa_educativo::where('nombreProgramaEducativo','Gestión de Proyectos')->first();
                $idGp=$gestion->id;
                $proEdu=Profesor::where('id_programa_educativo',$idGp)->get();
                $profesor= $proEdu;
                //dd($profesor);
                break;
            case 'Negocios y Gestión Empresarial':
                $negocios=Programa_educativo::where('nombreProgramaEducativo','Negocios y Gestión Empresarial')->first();
                $idNeg=$negocios->id;
                $proEdu=Profesor::where('id_programa_educativo',$idNeg)->get();
                $profesor= $proEdu;
                break;
            case 'Finanzas y Fiscal Contador Público':
                $finanzas=Programa_educativo::where('nombreProgramaEducativo','Finanzas y Fiscal Contador Público')->first();
                $idFina=$finanzas->id;
                $proEdu=Profesor::where('id_programa_educativo',$idFina)->get();
                $profesor= $proEdu;
                //dd($profesor);
                break;
            case 'Mecatrónica':
                $mecatrónica=Programa_educativo::where('nombreProgramaEducativo','Mecatrónica')->first();
                $idMeca=$mecatrónica->id;
                $proEdu=Profesor::where('id_programa_educativo',$idMeca)->get();
                $profesor= $proEdu;
                break;
            case 'Procesos Bioalimentarios':
                $procesos=Programa_educativo::where('nombreProgramaEducativo','Procesos Bioalimentarios')->first();
                $idProc=$procesos->id;
                $proEdu=Profesor::where('id_programa_educativo',$idProc)->get();
                $profesor= $proEdu;
                //dd($profesor);
                break;
            case 'Mantenimiento Industrial':
                $mantenimiento=Programa_educativo::where('nombreProgramaEducativo','Mantenimiento Industrial')->first();
                $idMant=$mantenimiento->id;
                $proEdu=Profesor::where('id_programa_educativo',$idMant)->get();
                $profesor= $proEdu;
                break;
            case 'Ingeniería Industrial':
                $industrial=Programa_educativo::where('nombreProgramaEducativo','Ingeniería Industrial')->first();
                $idTIndu=$industrial->id;
                $proEdu=Profesor::where('id_programa_educativo',$idTIndu)->get();
                $profesor= $proEdu;
                //dd($profesor);
                break;
            default:
                break;
        }
        return view('perfilDirector.profesores.main', compact('carrera','profesor','programasEducativos'));
    }

    public function mostrarPrefilD(Profesor $profesor){
        //dd($profesor);
       //obtiene una lista que es pasada al select de programas educativos
        $programasEducativos=Programa_educativo::orderBy('nombreProgramaEducativo','asc')->pluck('nombreProgramaEducativo','id');
        //obtiene una lista pasada al select de actividades
       $actividades = Actividad_extra::orderBy('nombre','asc')->pluck('nombre','id');
        //obtiene el id traido de la tabla
        $idProfesor = $profesor->id;
        //busca si existe una carga horaria con el id del profesor y la asigna a la variable $cargaHoraria
        $cargaHoraria= Carga_horaria::where('id_profesor',$idProfesor)->first();
        
        //regresa true si el objeto $cargaHoraria es null
        if(is_null($cargaHoraria)){
            //en caso de que retorne true solo mandara la variable profesor
            return view('perfilDirector.profesores.perfil',[
            'profesor' => $profesor,
            'programasEducativos'=> $programasEducativos,
            'actividades' => $actividades,
        ]);
        //de lo contrario pasara los objetos cargaHoraria y especialidad     
        }else{
             $id_carga_horaria=$cargaHoraria->id;
            //obtiene todas las asignaturas relacionadas al profesor
            $asignacionAsignaturas=$this->getAsignaturasProfesor($id_carga_horaria);
            //obtiene todas las actividades relacionadas al profesor
            $actividadesProfesor=$this->getActividadesProfesor($id_carga_horaria);
            //retorna la vista y pasa todas las variables
            return view('perfilDirector.profesores.perfil',[
            'profesor' => $profesor,
            'programasEducativos'=> $programasEducativos,
            'cargaHoraria' => $cargaHoraria,
            'asignacionAsignaturas'=>$asignacionAsignaturas,
            'actividades' => $actividades,
            'actividadesProfesor' => $actividadesProfesor,
        ]);
        
        }
    }

    public function nuevoProfesorD(Request $request){
        $carrera=Auth::user()->estado;
        $programasEducativos=Programa_educativo::where('nombreProgramaEducativo',$carrera)->first();
        $idCarrera=$programasEducativos->id;
        //try que realiza todo el código de guardado
        try{
        //crea una instancia del modelo para poder acceder a la base de datos 
        $profesor= new Profesor;
        //recibe los request ubicados en el formulario 
        $profesor->clave=$request->input('clave');
        $profesor->nombre=$request->input('nombre');
        $profesor->apellidoPaterno=$request->input('apellidoPaterno');
        $profesor->apellidoMaterno=$request->input('apellidoMaterno');
        $profesor->tipoProfesor='PTC';
        $profesor->id_programa_educativo=$idCarrera;
        //$profesor->id_programa_educativo=$request->get('programaEducativo');
        $profesor->save();
        
        return back()->with('success','Los datos han sido guardados correctamente');
        }catch(QueryException $ex){
            return redirect('/profesoresD')->with('status','Algunos datos no se han ingresado correctamente por favor intente de nuevo');
        }      
    }

    public function actualizarProfesorD(Request $request){
        $carrera=Auth::user()->estado;
        $programasEducativos=Programa_educativo::where('nombreProgramaEducativo',$carrera)->first();
        $idCarrera=$programasEducativos->id;
        //dd($idCarrera);
          try{
            $id =$request->input('id');
            $profesor = Profesor::find($id);  
            $profesor->clave = $request->input('clave');
            $profesor->nombre  = $request->input('nombre');
            $profesor->apellidoPaterno = $request->input('apellidoPaterno');
            $profesor->apellidoMaterno  = $request->input('apellidoMaterno');
            $profesor->tipoProfesor  = 'PTC';
            $profesor->id_programa_educativo  = $idCarrera;
            //dd($profesor);   
            $profesor->save();
            return back()->with('success','Los datos han sido actualizados correctamente');
            }catch(QueryException $ex){
                return redirect('/profesoresD')->with('status','Algunos datos no se han ingresado correctamente por favor intente de nuevo');
            }
    }

    public function eliminarProfesorD(Request $request){

        $id_profesor =$request->input('id');
        //Busca la carga horaria del profesor por medio de su id
        $cargaHoraria=Carga_horaria::where('id_profesor',$id_profesor);
        //encuentra al profesor por medio de su id
        $profesor = Profesor::findOrFail($id_profesor);
        //regresa true si el objeto $cargaHoraria es null
            if(is_null($cargaHoraria)){
                $profesor->delete();
             //regre a la vista con un mensaje de exito
                return back()->with('success','Los datos han sido eliminados correctamente');
            }else{
                $profesor->delete();
                $cargaHoraria->delete();
             //regre a la vista con un mensaje de exito
                return back()->with('success','Los datos han sido eliminados correctamente');
        }
    }

}
