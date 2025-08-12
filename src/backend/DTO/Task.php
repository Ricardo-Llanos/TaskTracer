<?php

class Task
{
    private ?int $Id_Task;
    private ?int $Id_User;
    private string $Name;
    private string $Status;
    private ?string $CreatedAt;
    private ?string $ModifiedAt;

    /***
     * Constructor de la clase Task
     * @param int| null $Id_Task ID de la tarea (Puede ser null en caso se quiera crear)
     * @param int $Id_User ID del usuario que creará u modificará la tarea
     * @param string $Name Nombre de la tarea a crear
     */
    function __construct(
        ?int $Id_Task = null,
        ?int $Id_User = null,
        string $Name
    ) {
        $this->Id_Task = $Id_Task;
        $this->Id_User = $Id_User;
        $this->Name = $Name;
        $this->Status = "To Do"; //Generar una configuración global de status
        $this->CreatedAt = null;
        $this->ModifiedAt = null;

        // $this->CreatedAt = date("d-m-y");
        // $this->ModifiedAt = date("d-m-y");
    }

    //===============Getters===============
    public function getId_Task(): ?int
    {
        return $this->Id_Task;
    }

    public function getId_User(): ?int
    {
        return $this->Id_User;
    }

    public function getName(): string
    {
        return $this->Name;
    }

    public function getStatus(): string
    {
        return $this->Status;
    }

    public function getCreatedAt(): ?string
    {
        return $this->CreatedAt;
    }

    public function getModifiedAt(): ?string
    {
        return $this->ModifiedAt;
    }

    //===============Setters===============
    public function setId_Task(int $Id_Task)
    {
        if ($Id_Task >= 0) {
            $this->Id_Task = $Id_Task;
        }
    }

    public function setId_User(int $Id_User)
    {
        if ($Id_User >= 0) {
            $this->Id_User = $Id_User;
        }
    }

    public function setName(string $Name)
    {
        $Name = trim($Name);

        if (empty($Name)) {
            echo "\n\n"."El valor que intentaste ingresar está vacío"."\n\n";
        } else {
            $this->Name = $Name;
        }
    }

    public function setStatus(string $Status)
    {
        $Status = trim($Status);
        $Status = ucwords(strtolower($Status)); //La primera letra de cada palabra se hará mayúscula

        $code = match($Status){
            'To Do', 'In Progress', 'Done'=> 1,
            default => 0
        };

        if (empty($Status)) {
            echo "\n\n"."El valor que intentaste ingresar está vacío"."\n\n";

        } elseif(!$code){
            echo "\n\n"."El valor que intentaste ingresar no se encuentra dentro de las opciones: To Do, In Pogress, Done"."\n\n";
            
        }else {
            $this->Status = $Status;
        }
    }

    public function setCreatedAt(string $CreatedAt)
    {
        $this->CreatedAt = $CreatedAt;
    }

    public function setModifiedAt(string $ModifiedAt)
    {
        $this->ModifiedAt = $ModifiedAt;
    }
}
