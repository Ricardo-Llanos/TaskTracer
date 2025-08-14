<?php
//Añadimos al psr-4
namespace backend\DTO;

class UserDTOGet{
    private ?int $pageNumber;
    private ?int $pageSize;
    private ?string $filterbyName;
    private ?string $filterbyPaternalSurname;
    private ?string $filterbyMaternalSurname;
    private ?string $filterbyEmail;
    private ?string $orderby;

    public function __construct(
        ?int $pageNumber = null,
        ?int $pageSize = null,
        ?string $filterbyName = null,
        ?string $filterbyPaternalSurname = null,
        ?string $filterbyMaternalSurname = null,
        ?string $filterbyEmail = null,
        ?string $orderby = null
    )
    {
        $this->pageNumber = $pageNumber;
        $this->pageSize = $pageSize;
        $this->filterbyName = $filterbyName;
        $this->filterbyPaternalSurname = $filterbyPaternalSurname;
        $this->filterbyMaternalSurname = $filterbyMaternalSurname;
        $this->filterbyEmail = $filterbyEmail;
        $this->orderby = $orderby;
    }

    /*==================================
                Getters
      ==================================*/
    public function getPageNumber(){
        return $this->pageNumber;
    }

    public function getPageSize(){
        return $this->pageSize;
    }

    public function getFilterbyName(){
        return $this->filterbyName;
    }

    public function getFilterbyPaternalSurname(){
        return $this->filterbyPaternalSurname;
    }
    
    public function getFilterbyMaternalSurname(){
        return $this->filterbyMaternalSurname;
    }

    public function getFilterbyEmail(){
        return $this->filterbyEmail;
    }

    public function getOrderby(){
        return $this->orderby;
    }
}
?>