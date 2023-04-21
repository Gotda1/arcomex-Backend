<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermisosTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table("permisos")->truncate();
        DB::table("rel_rol_permiso")->truncate();

        $this->usuarios();
        $this->clientes();
        $this->prospectos();
        $this->proveedores();
        $this->productos();
        $this->cotizaciones();
        $this->ordenesCompra();
        $this->cotizacionesCompra();
        $this->pedidos();
        $this->esProductos();
        $this->existencias();
        $this->dashboard();
    }

    private function usuarios()
    {
        DB::table("permisos")->insert([[
            "clave"       => "USRS",
            "nombre"      => "Usuarios",
            "descripcion" => "Usuarios",
            "papa"        => "NONE",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "INSUSR",
            "nombre"      => "Alta de usuarios",
            "descripcion" => "Alta de usuarios",
            "papa"        => "USRS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "UPDUSR",
            "nombre"      => "Edición de usuarios",
            "descripcion" => "Edición de usuarios",
            "papa"        => "USRS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "DELUSR",
            "nombre"      => "Eliminar usuarios",
            "descripcion" => "Eliminar usuarios",
            "papa"        => "USRS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ]]);
        
        
        DB::table("rel_rol_permiso")->insert([[
            "rol"     => "ADMN",
            "permiso" => "USRS"
        ], [
            "rol"     => "ADMN",
            "permiso" => "INSUSR"
        ], [
            "rol"     => "ADMN",
            "permiso" => "UPDUSR"
        ], [
            "rol"     => "ADMN",
            "permiso" => "DELUSR"
        ]]);
    }

    private function clientes()
    {
        DB::table("permisos")->insert([[
            "clave"       => "CLTS",
            "nombre"      => "Clientes",
            "descripcion" => "Clientes",
            "papa"        => "NONE",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "ALLCLT",
            "nombre"      => "Todos los clientes",
            "descripcion" => "Todos los clientes",
            "papa"        => "CLTS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
            
        ], [
            "clave"       => "INSCLT",
            "nombre"      => "Alta de clientes",
            "descripcion" => "Alta de clientes",
            "papa"        => "CLTS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "UPDCLT",
            "nombre"      => "Edición de clientes",
            "descripcion" => "Edición de clientes",
            "papa"        => "CLTS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "DELCLT",
            "nombre"      => "Eliminar clientes",
            "descripcion" => "Eliminar clientes",
            "papa"        => "CLTS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ]]);
        
        
        DB::table("rel_rol_permiso")->insert([[
            "rol"     => "ADMN",
            "permiso" => "CLTS"
        ], [
            "rol"     => "ADMN",
            "permiso" => "ALLCLT"
        ], [
            "rol"     => "ADMN",
            "permiso" => "INSCLT"
        ], [
            "rol"     => "ADMN",
            "permiso" => "UPDCLT"
        ], [
            "rol"     => "ADMN",
            "permiso" => "DELCLT"
        ],[
            "rol"     => "VTAS",
            "permiso" => "CLTS"
        ], [
            "rol"     => "VTAS",
            "permiso" => "INSCLT"
        ], [
            "rol"     => "VTAS",
            "permiso" => "UPDCLT"
        ], [
            "rol"     => "VTAS",
            "permiso" => "DELCLT"
        ], [
            "rol"     => "CONT",
            "permiso" => "CLTS"
        ], [
            "rol"     => "CONT",
            "permiso" => "ALLCLT"
        ]]);
    }

    private function prospectos()
    {
        DB::table("permisos")->insert([[
            "clave"       => "PRSPS",
            "nombre"      => "Prospectos",
            "descripcion" => "Prospectos",
            "papa"        => "NONE",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "ALLPRSP",
            "nombre"      => "Todos los prospectos",
            "descripcion" => "Todos los prospectos",
            "papa"        => "PRSPS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
            
        ], [
            "clave"       => "INSPRSP",
            "nombre"      => "Alta de prospectos",
            "descripcion" => "Alta de prospectos",
            "papa"        => "PRSPS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "UPDPRSP",
            "nombre"      => "Edición de prospectos",
            "descripcion" => "Edición de prospectos",
            "papa"        => "PRSPS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "DELPRSP",
            "nombre"      => "Eliminar prospectos",
            "descripcion" => "Eliminar prospectos",
            "papa"        => "PRSPS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ]]);
        
        
        DB::table("rel_rol_permiso")->insert([[
            "rol"     => "ADMN",
            "permiso" => "PRSPS"
        ], [
            "rol"     => "ADMN",
            "permiso" => "ALLPRSP"
        ], [
            "rol"     => "ADMN",
            "permiso" => "INSPRSP"
        ], [
            "rol"     => "ADMN",
            "permiso" => "UPDPRSP"
        ], [
            "rol"     => "ADMN",
            "permiso" => "DELPRSP"
        ],[
            "rol"     => "VTAS",
            "permiso" => "PRSPS"
        ], [
            "rol"     => "VTAS",
            "permiso" => "INSPRSP"
        ], [
            "rol"     => "VTAS",
            "permiso" => "UPDPRSP"
        ], [
            "rol"     => "VTAS",
            "permiso" => "DELPRSP"
        ], [
            "rol"     => "CONT",
            "permiso" => "PRSPS"
        ], [
            "rol"     => "CONT",
            "permiso" => "ALLPRSP"
        ]]);
    }

    private function proveedores()
    {
        DB::table("permisos")->insert([[
            "clave"       => "PVDS",
            "nombre"      => "Proveedores",
            "descripcion" => "Proveedores",
            "papa"        => "NONE",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "INSPVD",
            "nombre"      => "Alta de proveedores",
            "descripcion" => "Alta de proveedores",
            "papa"        => "PVDS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "UPDPVD",
            "nombre"      => "Edición de proveedores",
            "descripcion" => "Edición de proveedores",
            "papa"        => "PVDS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "DELPVD",
            "nombre"      => "Eliminar proveedores",
            "descripcion" => "Eliminar proveedores",
            "papa"        => "PVDS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ]]);
        
        
        DB::table("rel_rol_permiso")->insert([[
            "rol"     => "ADMN",
            "permiso" => "PVDS"
        ], [
            "rol"     => "ADMN",
            "permiso" => "INSPVD"
        ], [
            "rol"     => "ADMN",
            "permiso" => "UPDPVD"
        ], [
            "rol"     => "ADMN",
            "permiso" => "DELPVD"
        ],[
            "rol"     => "COMP",
            "permiso" => "PVDS"
        ], [
            "rol"     => "COMP",
            "permiso" => "INSPVD"
        ], [
            "rol"     => "COMP",
            "permiso" => "UPDPVD"
        ], [
            "rol"     => "COMP",
            "permiso" => "DELPVD"
        ], [
            "rol"     => "CONT",
            "permiso" => "PVDS"
        ]]);
    }

    private function productos()
    {
        DB::table("permisos")->insert([[
            "clave"       => "PRODS",
            "nombre"      => "Productos",
            "descripcion" => "Productos",
            "papa"        => "NONE",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "INSPRD",
            "nombre"      => "Alta de productos",
            "descripcion" => "Alta de productos",
            "papa"        => "PRODS",
            "asignable"   => 0,
            "depende"     => "PRODS",
            "orden"       => 0,
        ], [
            "clave"       => "UPDPRD",
            "nombre"      => "Edición de productos",
            "descripcion" => "Edición de productos",
            "papa"        => "PRODS",
            "asignable"   => 0,
            "depende"     => "PRODS",
            "orden"       => 0,
        ], [
            "clave"       => "PRCPROD",
            "nombre"      => "Precio de productos",
            "descripcion" => "Precio de productos",
            "papa"        => "PRODS",
            "asignable"   => 0,
            "depende"     => "PRODS",
            "orden"       => 0,
        ], [
            "clave"       => "DELPRD",
            "nombre"      => "Eliminar productos",
            "descripcion" => "Eliminar productos",
            "papa"        => "PRODS",
            "asignable"   => 0,
            "depende"     => "PRODS",
            "orden"       => 0,
        ]]);
        
        
        DB::table("rel_rol_permiso")->insert([[
            "rol"     => "ADMN",
            "permiso" => "PRODS"
        ], [
            "rol"     => "ADMN",
            "permiso" => "INSPRD"
        ], [
            "rol"     => "ADMN",
            "permiso" => "UPDPRD"
        ], [
            "rol"     => "ADMN",
            "permiso" => "PRCPROD"
        ], [
            "rol"     => "VTAS",
            "permiso" => "PRCPROD"
        ],[
            "rol"     => "ADMN",
            "permiso" => "DELPRD"
        ],[
            "rol"     => "VTAS",
            "permiso" => "PRODS"
        ],[
            "rol"     => "COMP",
            "permiso" => "PRODS"
        ],[
            "rol"     => "COMP",
            "permiso" => "INSPRD"
        ],[
            "rol"     => "COMP",
            "permiso" => "UPDPRD"
        ],[
            "rol"     => "CONT",
            "permiso" => "PRODS"
        ],[
            "rol"     => "CONT",
            "permiso" => "PRCPROD"
        ]]);


        
    }

    private function existencias(){
        DB::table("permisos")->insert([[
            "clave"       => "EXSPRD",
            "nombre"      => "Existencias de productos",
            "descripcion" => "Existencias de productos",
            "papa"        => "",
            "asignable"   => 0,
            "depende"     => "",
            "orden"       => 0,
        ]]);

        DB::table("rel_rol_permiso")->insert([[
            "rol"     => "ADMN",
            "permiso" => "EXSPRD"
        ], [
            "rol"     => "COMP",
            "permiso" => "EXSPRD"
        ], [
            "rol"     => "ALMC",
            "permiso" => "EXSPRD"
        ], [
            "rol"     => "CONT",
            "permiso" => "EXSPRD"
        ]]);
    }

    private function esProductos(){
        DB::table("permisos")->insert([[
            "clave"       => "ESPRD",
            "nombre"      => "Entradas y salidas de productos",
            "descripcion" => "Entradas y salidas de productos",
            "papa"        => "",
            "asignable"   => 0,
            "depende"     => "",
            "orden"       => 0,
        ]]);

        DB::table("rel_rol_permiso")->insert([[
            "rol"     => "ADMN",
            "permiso" => "ESPRD"
        ]]);
    }

    private function cotizaciones(){
        DB::table("permisos")->insert([[
            "clave"       => "COTZ",
            "nombre"      => "Cotizaciones",
            "descripcion" => "Cotizaciones",
            "papa"        => "NONE",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "INSCOT",
            "nombre"      => "Alta de cotizaciones",
            "descripcion" => "Alta de cotizaciones",
            "papa"        => "COTZ",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "UPDCOT",
            "nombre"      => "Edición de cotizaciones",
            "descripcion" => "Edición de cotizaciones",
            "papa"        => "COTZ",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "PDOCOT",
            "nombre"      => "Pasar a pedido",
            "descripcion" => "Pasar a pedido",
            "papa"        => "COTZ",
            "asignable"   => 0,
            "depende"     => "COTZ",
            "orden"       => 0,
        ], [
            "clave"       => "CANCOT",
            "nombre"      => "Cancelar cotización",
            "descripcion" => "Cancelar cotización",
            "papa"        => "COTZ",
            "asignable"   => 0,
            "depende"     => "COTZ",
            "orden"       => 0,
        ], [
            "clave"       => "DELCOT",
            "nombre"      => "Eliminar cotizaciones",
            "descripcion" => "Eliminar cotizaciones",
            "papa"        => "COTZ",
            "asignable"   => 0,
            "depende"     => "COTZ",
            "orden"       => 0,
        ]]);
        
        
        DB::table("rel_rol_permiso")->insert([[
            "rol"     => "ADMN",
            "permiso" => "COTZ"
        ],[
            "rol"     => "ADMN",
            "permiso" => "INSCOT"
        ],[
            "rol"     => "ADMN",
            "permiso" => "UPDCOT"
        ],[
            "rol"     => "ADMN",
            "permiso" => "PDOCOT"
        ],[
            "rol"     => "ADMN",
            "permiso" => "CANCOT"
        ],[
            "rol"     => "ADMN",
            "permiso" => "DELCOT"
        ],[
            "rol"     => "VTAS",
            "permiso" => "COTZ"
        ],[
            "rol"     => "VTAS",
            "permiso" => "INSCOT"
        ],[
            "rol"     => "VTAS",
            "permiso" => "UPDCOT"
        ],[
            "rol"     => "VTAS",
            "permiso" => "PDOCOT"
        ],[
            "rol"     => "VTAS",
            "permiso" => "CANCOT"
        ],[
            "rol"     => "CONT",
            "permiso" => "COTZ"
        ]]);
    }

    private function ordenesCompra(){
        DB::table("permisos")->insert([[
            "clave"       => "OCMPS",
            "nombre"      => "Órdenes de compra",
            "descripcion" => "Órdenes de compra",
            "papa"        => "NONE",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "INSOCMP",
            "nombre"      => "Alta de órden de compra",
            "descripcion" => "Alta de órden de compra",
            "papa"        => "OCMPS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ],  [
            "clave"       => "UPDOCMP",
            "nombre"      => "Editar órden de compra",
            "descripcion" => "Editar órden de compra",
            "papa"        => "OCMPS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "CANOCMP",
            "nombre"      => "Cancelar órden de compra",
            "descripcion" => "Cancelar órden de compra",
            "papa"        => "OCMPS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "SRTOCMP",
            "nombre"      => "Surtir órden de compra",
            "descripcion" => "Surtir órden de compra",
            "papa"        => "OCMPS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "DELOCMP",
            "nombre"      => "Eliminar órden de compra",
            "descripcion" => "Eliminar órden de compra",
            "papa"        => "OCMPS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "PRCOCMP",
            "nombre"      => "Precios órden de compra",
            "descripcion" => "Precios órden de compra",
            "papa"        => "OCMPS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ]]);
        
        
        DB::table("rel_rol_permiso")->insert([[
            "rol"     => "ADMN",
            "permiso" => "OCMPS"
        ],[
            "rol"     => "ADMN",
            "permiso" => "INSOCMP"
        ],[
            "rol"     => "ADMN",
            "permiso" => "UPDOCMP"
        ],[
            "rol"     => "ADMN",
            "permiso" => "CANOCMP"
        ],[
            "rol"     => "ADMN",
            "permiso" => "SRTOCMP"
        ], [
            "rol"     => "ADMN",
            "permiso" => "DELOCMP"
        ],[
            "rol"     => "ADMN",
            "permiso" => "PRCOCMP"
        ], [
            "rol"     => "COMP",
            "permiso" => "OCMPS"
        ],[
            "rol"     => "COMP",
            "permiso" => "INSOCMP"
        ],[
            "rol"     => "COMP",
            "permiso" => "UPDOCMP"
        ],[
            "rol"     => "COMP",
            "permiso" => "CANOCMP"
        ],[
            "rol"     => "COMP",
            "permiso" => "SRTOCMP"
        ],[
            "rol"     => "COMP",
            "permiso" => "PRCOCMP"
        ],[
            "rol"     => "ALMC",
            "permiso" => "PRCOCMP"
        ],[
            "rol"     => "CONT",
            "permiso" => "OCMPS"
        ]]);
    }

    private function cotizacionesCompra(){
        DB::table("permisos")->insert([[
            "clave"       => "CCMPS",
            "nombre"      => "Cotizaciones de compra",
            "descripcion" => "Cotizaciones de compra",
            "papa"        => "NONE",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "INSCCMP",
            "nombre"      => "Alta de cotización de compra",
            "descripcion" => "Alta de cotización de compra",
            "papa"        => "CCMPS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ],  [
            "clave"       => "UPDCCMP",
            "nombre"      => "Editar cotización de compra",
            "descripcion" => "Editar cotización de compra",
            "papa"        => "CCMPS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "CANCCMP",
            "nombre"      => "Cancelar cotización de compra",
            "descripcion" => "Cancelar cotización de compra",
            "papa"        => "CCMPS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "DELCCMP",
            "nombre"      => "Eliminar cotización de compra",
            "descripcion" => "Eliminar cotización de compra",
            "papa"        => "CCMPS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ]]);
        
        
        DB::table("rel_rol_permiso")->insert([[
            "rol"     => "ADMN",
            "permiso" => "CCMPS"
        ],[
            "rol"     => "ADMN",
            "permiso" => "INSCCMP"
        ],[
            "rol"     => "ADMN",
            "permiso" => "UPDCCMP"
        ],[
            "rol"     => "ADMN",
            "permiso" => "CANCCMP"
        ], [
            "rol"     => "ADMN",
            "permiso" => "DELCCMP"
        ], [
            "rol"     => "COMP",
            "permiso" => "CCMPS"
        ],[
            "rol"     => "COMP",
            "permiso" => "INSCCMP"
        ],[
            "rol"     => "COMP",
            "permiso" => "UPDCCMP"
        ],[
            "rol"     => "COMP",
            "permiso" => "CANCCMP"
        ],[
            "rol"     => "CONT",
            "permiso" => "CCMPS"
        ],[
            "rol"     => "CONT",
            "permiso" => "INSCCMP"
        ],[
            "rol"     => "CONT",
            "permiso" => "UPDCCMP"
        ]]);
    }

    private function pedidos(){
        DB::table("permisos")->insert([[
            "clave"       => "PDDOS",
            "nombre"      => "Pedidos",
            "descripcion" => "Pedidos",
            "papa"        => "NONE",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "INSPDD",
            "nombre"      => "Alta de pedidos",
            "descripcion" => "Alta de pedidos",
            "papa"        => "PDDOS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "CANPDD",
            "nombre"      => "Cancelar pedidos",
            "descripcion" => "Cancelar pedidos",
            "papa"        => "PDDOS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "DELPDD",
            "nombre"      => "Eliminar pedidos",
            "descripcion" => "Eliminar pedidos",
            "papa"        => "PDDOS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ],[
            "clave"       => "SURPDD",
            "nombre"      => "Surtir pedidos",
            "descripcion" => "Surtir pedidos",
            "papa"        => "PDDOS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ],[
            "clave"       => "PAGPDD",
            "nombre"      => "Pago pedidos",
            "descripcion" => "Pago pedidos",
            "papa"        => "PDDOS",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "PDFALM",
            "nombre"      => "Pdf almacén",
            "descripcion" => "Pdf almacén",
            "papa"        => "OCMPS",
            "asignable"   => 0,
            "depende"     => "PDDOS",
            "orden"       => 0,
        ], [
            "clave"       => "PDFCMP",
            "nombre"      => "Pdf compras",
            "descripcion" => "Pdf compras",
            "papa"        => "PDDOS",
            "asignable"   => 0,
            "depende"     => "PDDOS",
            "orden"       => 0,
        ], [
            "clave"       => "PDFCLTE",
            "nombre"      => "Pdf cliente",
            "descripcion" => "Pdf cliente",
            "papa"        => "PDDOS",
            "asignable"   => 0,
            "depende"     => "PDDOS",
            "orden"       => 0,
        ]]);
        
        
        DB::table("rel_rol_permiso")->insert([[
            "rol"     => "ADMN",
            "permiso" => "PDDOS"
        ],[
            "rol"     => "ADMN",
            "permiso" => "INSPDD"
        ],[
            "rol"     => "ADMN",
            "permiso" => "CANPDD"
        ],[
            "rol"     => "ADMN",
            "permiso" => "DELPDD"
        ],[
            "rol"     => "VTAS",
            "permiso" => "PDDOS"
        ],[
            "rol"     => "COMP",
            "permiso" => "PDDOS"
        ],[
            "rol"     => "ALM",
            "permiso" => "PDDOS"
        ],[
            "rol"     => "ADMN",
            "permiso" => "SURPDD"
        ],[
            "rol"     => "COMP",
            "permiso" => "SURPDD"
        ],[
            "rol"     => "ADMN",
            "permiso" => "PAGPDD"  
        ],[
            "rol"     => "VTAS",
            "permiso" => "INSPDD"
        ],[
            "rol"     => "ALMC",
            "permiso" => "DELPDD"
        ],[
            "rol"     => "COMP",
            "permiso" => "PDFCMP"
        ],[
            "rol"     => "ALMC",
            "permiso" => "PDFALM"
        ],[
            "rol"     => "VTAS",
            "permiso" => "PDFCLTE"
        ],[
            "rol"     => "VTAS",
            "permiso" => "PDFCMP"
        ],[
            "rol"     => "ADMN",
            "permiso" => "PDFALM"
        ],[
            "rol"     => "VTAS",
            "permiso" => "PDFALM"
        ],[
            "rol"     => "COMP",
            "permiso" => "PDFALM"
        ],[
            "rol"     => "ADMN",
            "permiso" => "PDFCLTE"
        ],[
            "rol"     => "ADMN",
            "permiso" => "PDFCMP"
        ],[
            "rol"     => "CONT",
            "permiso" => "PDDOS"
        ],[
            "rol"     => "CONT",
            "permiso" => "INSPDD"
        ],[
            "rol"     => "CONT",
            "permiso" => "PAGPDD"
        ],[
            "rol"     => "CONT",
            "permiso" => "PDFCLTE"
        ],[
            "rol"     => "CONT",
            "permiso" => "PDFCMP"
        ],[
            "rol"     => "CONT",
            "permiso" => "PDFALM"
        ]]);
    }

    private function dashboard(){
        DB::table("permisos")->insert([[
            "clave"       => "REPPRODPROV",
            "nombre"      => "Reporte productos proveedores",
            "descripcion" => "Reporte productos proveedores",
            "papa"        => "NONE",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "REPCLTES",
            "nombre"      => "Reporte clientes",
            "descripcion" => "Reporte clientes",
            "papa"        => "NONE",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ],[
            "clave"       => "REPCOTS",
            "nombre"      => "Reporte cotizaciones",
            "descripcion" => "Reporte cotizaciones",
            "papa"        => "NONE",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "REPEXIS",
            "nombre"      => "Reporte existencias",
            "descripcion" => "Reporte existencias",
            "papa"        => "NONE",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "REPPDD",
            "nombre"      => "Reporte pedidos",
            "descripcion" => "Reporte pedidos",
            "papa"        => "NONE",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "REPALM",
            "nombre"      => "Reporte almacén",
            "descripcion" => "Reporte almacén",
            "papa"        => "NONE",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "REPPROVMOV",
            "nombre"      => "Reporte movimientos proveedores",
            "descripcion" => "Reporte movimientos proveedores",
            "papa"        => "NONE",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ], [
            "clave"       => "REPCLIMOV",
            "nombre"      => "Reporte movimientos clientes",
            "descripcion" => "Reporte movimientos clientes",
            "papa"        => "NONE",
            "asignable"   => 0,
            "depende"     => "NONE",
            "orden"       => 0,
        ]]);

        DB::table("rel_rol_permiso")->insert([[
            "rol"     => "ADMN",
            "permiso" => "REPPRODPROV"
        ],[
            "rol"     => "COMP",
            "permiso" => "REPPRODPROV"
        ],[
            "rol"     => "ADMN",
            "permiso" => "REPCLTES"
        ],[
            "rol"     => "VTAS",
            "permiso" => "REPCLTES"
        ],[
            "rol"     => "ADMN",
            "permiso" => "REPCOTS"
        ],[
            "rol"     => "VTAS",
            "permiso" => "REPCOTS"
        ],[
            "rol"     => "ADMN",
            "permiso" => "REPEXIS"
        ],[
            "rol"     => "COMP",
            "permiso" => "REPEXIS"
        ],[
            "rol"     => "ALMC",
            "permiso" => "REPEXIS"
        ],[
            "rol"     => "ADMN",
            "permiso" => "REPPDD"
        ],[
            "rol"     => "VTAS",
            "permiso" => "REPPDD"
        ],[
            "rol"     => "COMP",
            "permiso" => "REPALM"
        ],[
            "rol"     => "ADMN",
            "permiso" => "REPALM"
        ],[
            "rol"     => "COMP",
            "permiso" => "REPPROVMOV"
        ],[
            "rol"     => "ADMN",
            "permiso" => "REPPROVMOV"
        ],[
            "rol"     => "VTAS",
            "permiso" => "REPCLIMOV"
        ],[
            "rol"     => "ADMN",
            "permiso" => "REPCLIMOV"
        ],[
            "rol"     => "CONT",
            "permiso" => "REPPRODPROV"
        ] ,[
            "rol"     => "CONT",
            "permiso" => "REPCLTES"
        ] ,[
            "rol"     => "CONT",
            "permiso" => "REPCOTS"
        ] ,[
            "rol"     => "CONT",
            "permiso" => "REPEXIS"
        ] ,[
            "rol"     => "CONT",
            "permiso" => "REPPDD"
        ] ,[
            "rol"     => "CONT",
            "permiso" => "REPALM"
        ] ,[
            "rol"     => "CONT",
            "permiso" => "REPPROVMOV"
        ] ,[
            "rol"     => "CONT",
            "permiso" => "REPCLIMOV"
        ]]);
    }
}

