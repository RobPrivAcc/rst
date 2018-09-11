<?php
class Product{
    private $pdo = null;
    private $productArray = null;
    private $randomArray = null;
    private $minusStockArray = null;
    private $topProducts = null;
    
    public function __construct($pdo){
        $this->pdo = $pdo;
        
        $excl = "";
        $exclusionType = array('% Livestock');
        $exclusionProduct = array('% (Pre-Bagged)','% (30x80cm)');
        
        for($i=0; $i<count($exclusionType);$i++){
            $excl .= "[Type of Item] not like '$exclusionType[$i]' AND ";
        }
        
        for($i=0; $i<count($exclusionProduct);$i++){
            $excl .= "[Name of Item] not like '$exclusionProduct[$i]' AND ";
        }
        
        
        $sql = $this->pdo->prepare("SELECT [Name of Item] FROM Stock WHERE ".$excl."Discontinued = '0';");
        $sql->execute();
        
        while($row = $sql->fetch()){
            $this->productArray[] = $row['Name of Item'];
        }
    }
    
    public function countProducts(){
        
        $count = $this->pdo->sql("SELECT count(1) FROM Stock WHERE Discontinued = '0';")->fetchColumn();
        return $count;
    }
    
    private function createRandomArray(){
        $this->randomArray = null;
        for($i=0; $i < 20;$i++){
            $this->randomArray[] = $this->productArray[mt_rand(0,count($this->productArray,0))];
        }
    }
    
    public function displayRandomArray(){
        $this->createRandomArray();
        for($i=0; $i<count($this->randomArray);$i++){
            echo $this->randomArray[$i].'<br/>';
        }
    }
    
    public function createMinusStockArray(){
        $sql = $this->pdo->prepare("SELECT [Name of Item] FROM Stock WHERE [Quantity] < '0';");
        $sql->execute();
        
        while($row = $sql->fetch()){
            $this->minusStockArray[] = $row['Name of Item'];
        }
        print_r($this->minusStockArray);
    }
    
    public function topProducts($x){
        $dateToday = date("Y-m-d");
   
        $dateFrom = date("Y-m-d", strtotime('-1 month'));
        
        $query = "SELECT Distinct([NameOfItem]) as [product],SUM([QuantityBought]) as Total
                    FROM [Orders] inner join [Days] on [Order Number] = [OrderNo]
                    WHERE [Date] > '".$dateFrom."' and [Date] < '".$dateToday."' AND [NameOfItem] not like '%*%'
                    Group by [NameOfItem] ORDER BY [Total] DESC";
                    
        $sql = $this->pdo->prepare($query);
        $sql->execute();
        $i=0;
        while($row = $sql->fetch()){
            if($i<$x){
                $this->topProducts[] = $row['product'];
                $i++;
            }
        }
        return $this->topProducts;
    }
}
?>