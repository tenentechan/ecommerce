<?php 
namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;
class Category extends Model {
    public static function listAll()
    {
        $sql = new Sql();
        return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
    }

    public function save()
    {
        $sql = new Sql();
        $results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
            ":idcategory"=>$this->getidcategory(),
            ":descategory"=>$this->getdescategory()
        ));
        $this->setData($results[0]);
        Category::updateFile();
    }

    public function get($idcategory)
    {
        $sql = new Sql();
        $results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory", [
            ':idcategory'=>$idcategory
        ]);
        $this->setData($results[0]);
    }

    public function delete()
    {
        $sql = new Sql();
        $sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory", [
            ':idcategory'=>$this->getidcategory()
        ]);
        Category::updateFile();
    }
    
    public static function updateFile()
    {
        $categories = Category::listAll();
        $html = [];
        foreach ($categories as $row) {
            array_push($html, '<li><a href="/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
        }
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . "categories-menu.html", implode('', $html));
    }

    /* method para trazer todos os produtos do BD na tela Adm 
     * por padrão o boleano $related = true, trará todos que 
     * estão relacionados com a categoria do produto.
     */
    public function getProducts($related = true)
    {
        $sql = new Sql();
        if ($related === true) {
            return $sql->select("
                SELECT * FROM tb_products WHERE idproduct IN(
                    SELECT a.idproduct
                    FROM tb_products a
                    INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
                    WHERE b.idcategory = :idcategory
                );
            ", [
                ':idcategory'=>$this->getidcategory()
            ]);
        } else {
            return $sql->select("
                SELECT * FROM tb_products WHERE idproduct NOT IN(
                    SELECT a.idproduct
                    FROM tb_products a
                    INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
                    WHERE b.idcategory = :idcategory
                );
            ", [
                ':idcategory'=>$this->getidcategory()
            ]);
        }
    }

    /* Paginacao
     * $page: Em qual pagina estamos
     * $itemsPerPage: Quantos itens por paginas nos queremos
     */
    public function getProductsPage($page = 1, $itemsPerPage = 8)
    {
        /* Exemplo::
         * 1($page)-1 = 0 ==> * 3($itemPerPage) == 0
         * Começa no registro 0 e me traga 3
         * 2($page)-1 = 1 ==> * 3($itemPerPage) == 3
         * Começa no registro 3 e me traga 3
         * 3($page)-1 = 2 ==> * 3($itemPerPage) == 6
         * Começa no registro 6 e me traga 3
         */

        $start = ($page - 1) * $itemsPerPage;
        $sql = new Sql();
        $results = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS *
            FROM tb_products a
            INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
            INNER JOIN tb_categories c ON c.idcategory = b.idcategory
            WHERE c.idcategory = :idcategory
            LIMIT $start, $itemsPerPage;
        ", [
            ':idcategory'=>$this->getidcategory()
        ]);

        /* Verifica quantos itens há na categoria */
        $resultTotal = $sql->select("SELECT FOUND_ROWS() AS nrtotal;");
        return [
            /*
             * $results: está guardando os dados do produto
             */
            'data'=>Product::checkList($results),
            /*
             * [0]: traga a partir da posição "0 (Zero)"
             * ["nrtotal"]: Traga a coluna total
             */
            'total'=>(int)$resultTotal[0]["nrtotal"],
            /*
             * Quantas páginas foram geradas;
             * ceil: arredonta para inteiro =>
             * Exemplo: Se temos 11 registros e devem aparecer 10 por página
             * o último registro não apareceria.
             */
            'pages'=>ceil($resultTotal[0]["nrtotal"] / $itemsPerPage)
        ];
    }


    public function addProduct(Product $product)
    {
        $sql = new Sql();
        $sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES(:idcategory, :idproduct)", [
            ':idcategory'=>$this->getidcategory(),
            ':idproduct'=>$product->getidproduct()
        ]);
    }

    public function removeProduct(Product $product)
    {
        $sql = new Sql();
        $sql->query("DELETE FROM tb_productscategories 
            WHERE
            idcategory  = :idcategory
            AND
            idproduct   = :idproduct", [
                ':idcategory'   =>$this->getidcategory(),
                ':idproduct'    =>$product->getidproduct()
            ]);
    }


    public static function getPage($page = 1, $itemsPerPage = 10)
    {
        $start = ($page - 1) * $itemsPerPage;
        $sql = new Sql();
        $results = $sql->select("
            SELECT SQL_CALC_FOUND_ROWS *
            FROM tb_categories 
            ORDER BY descategory
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
            FROM tb_categories 
            WHERE descategory LIKE :search 
            ORDER BY descategory 
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