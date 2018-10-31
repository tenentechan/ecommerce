<?php
namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;

class Product extends Model
{
    public static function listAll() 
    {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_products ORDER BY desproduct");
    }

    /* 
     * Listando os produtos:
     * method static para trazer as imagens do servidor, pois elas não estao 
     * no BD.
    */
    public static function checkList($list)
    {
        foreach ($list as &$row) {
            $p = new Product();
            $p->setData($row);
            $row = $p->getValues();
        }
        return $list;
    }


    public function save()
	{
        $sql = new Sql();
        $results = $sql->select("CALL sp_products_save(:idproduct, :desproduct, :vlprice, :vlwidth, :vlheight, :vllength, :vlweight, :desurl)", array(
            ":idproduct"=>$this->getidproduct(),
            ":desproduct"=>$this->getdesproduct(),
            ":vlprice"=>$this->getvlprice(),
            ":vlwidth"=>$this->getvlwidth(),
            ":vlheight"=>$this->getvlheight(),
            ":vllength"=>$this->getvllength(),
            ":vlweight"=>$this->getvlweight(),
            ":desurl"=>$this->getdesurl()
        ));
        $this->setData($results[0]);

    }

    public function get($idproduct)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_products WHERE idproduct = :idproduct", [
            ':idproduct'=>$idproduct
        ]);
        $this->setData($results[0]);
    }

    public function delete()
    {
        $sql = new Sql();
        $sql->query("DELETE FROM tb_products WHERE idproduct = :idproduct", [
            ':idproduct'=>$this->getidproduct()
        ]);
    }   

 

    public function checkPhoto()
    {
        if (file_exists(
            $_SERVER['DOCUMENT_ROOT'] 
                        . DIRECTORY_SEPARATOR . 
            "res"       . DIRECTORY_SEPARATOR . 
            "site"      . DIRECTORY_SEPARATOR . 
            "img"       . DIRECTORY_SEPARATOR . 
            "products"  . DIRECTORY_SEPARATOR . 
            $this->getidproduct() . ".jpg"
        )) {  
            $url = "/res/site/img/products/" . $this->getidproduct() . ".jpg";
        } else {
            $url = "/res/site/img/product.jpg";
        }
        return $this->setdesphoto($url);
    }

    public function getValues()
    {
        $this->checkPhoto();
        $values = parent::getValues();
        return $values;
    }

    public function setPhoto($file)
    {
        /* Identificar imagem criada pelo usuário, convertê-la para jpg */
        /* 
         * explode: vao procurar o ". (ponto)" no arquivo converterá para array
         * end: deve pegar apenas o que vier após o ponto.
         */
        $extension = explode('.', $file['name']);
        $extension = end($extension);

        switch ($extension) {
            case "jpg":
            case "jpeg":
            $image = imagecreatefromjpeg($file["tmp_name"]);
            break;
            
            case "gif":
            $image = imagecreatefromgif($file["tmp_name"]);
            break;

            case "png":
            $image = imagecreatefrompng($file["tmp_name"]);
            break;
        }

       $dist = $_SERVER['DOCUMENT_ROOT'] 
                        . DIRECTORY_SEPARATOR . 
            "res"       . DIRECTORY_SEPARATOR . 
            "site"      . DIRECTORY_SEPARATOR . 
            "img"       . DIRECTORY_SEPARATOR . 
            "products"  . DIRECTORY_SEPARATOR . 
            $this->getidproduct() . ".jpg";


            imagejpeg($image, $dist);

            /* Destruindo a imagem */
            imagedestroy($image);

            /* Carregando a imagem na memoria */
            $this->checkPhoto();
    }

    public function getFromURL($desurl)
    {
        $sql = new Sql();
        $rows = $sql->select("SELECT * FROM tb_products WHERE desurl = :desurl", [
            ':desurl'=>$desurl
        ]);
        $this->setData($rows[0]);
    }

    public function getCategories()
    {
        $sql = new Sql();
        return $sql->select("
            SELECT * FROM tb_categories a INNER JOIN tb_productscategories b ON a.idcategory = b.idcategory WHERE b.idproduct = :idproduct
        ", [
            ':idproduct'=>$this->getidproduct()
        ]);
    }



    public static function getPage($page = 1, $itemsPerPage = 10)
    {
        $start = ($page - 1) * $itemsPerPage;
        $sql = new Sql();
        $results = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS *
            FROM tb_products 
            ORDER BY desproduct
            LIMIT $start, $itemsPerPage;
        ");
        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
        return [
            'data'=>$results,
            'total'=>(int)$resultTotal[0]["nrtotal"],
            'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
        ];
    }

    public static function getPageSearch($search, $page = 1, $itemsPerPage = 10)
    {
        $start = ($page - 1) * $itemsPerPage;
        $sql = new Sql();
        $results = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS *
            FROM tb_products 
            WHERE desproduct LIKE :search 
            ORDER BY desproduct
            LIMIT $start, $itemsPerPage;
        ", [
            ':search'=>'%'.$search.'%'
        ]);
        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
        return [
            'data'=>$results,
            'total'=>(int)$resultTotal[0]["nrtotal"],
            'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
        ];
    }

}

?>