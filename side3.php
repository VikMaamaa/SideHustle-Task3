<?php 

//class that houses methods for adding, deleting and setting status of todos
//class also performs connection to database, 
//if database does not exist, it creates one automatically
class Todo{
    public $servername;
    public $username;
    public $password;
    public $db;
    public $conn;
    public $i;

    //variable to flag if connection to database was successful and prevent creating same instance of class
    public static $count;


    function __construct($servername, $username, $password, $db)
    {
        //initialize class properties
        $this->servername = $servername;
        $this->username = $username;
        $this->password = $password;
        $this->db = $db;
        $this::$count = 0;
        
        // Create connection
        $this->conn = new mysqli($this->servername, $this->username, $this->password);
            // Check connection
        if ($this->conn ->connect_error) {
            die("Connection failed: " . $this->conn ->connect_error);//stop execution and generate error
        } 
        
        //if connection successful, check if database exist
        if (empty(mysqli_fetch_array($this->conn ->query("SHOW DATABASES LIKE '$this->db'")))) {
            echo "Database does not exist ";
            //Automatically Create database if database does not exist
                $sql = "CREATE DATABASE ".$this->db;
                if ($this->conn ->query($sql) === TRUE) {
                   
                      // Create connection to the newly created database
                    $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->db);
                    
                    // Check connection
                    if ($this->conn ->connect_error) {
                        die("Connection failed: " . $this->conn ->connect_error);
                    } 
                    echo "Database created successfully";
                    //initialize count to 1
                    $this::$count = 1;

                    //creates table in database
                    $sql="CREATE TABLE `todo` (`todo_id` int(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, `todo` varchar(150) NOT NULL, `status` varchar(150) NOT NULL ) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
                    $this->conn ->query($sql) ;
                } else {
                    //generates error if database could not be created
                    echo "Error creating database: " . $this->conn ->error;
                }
        }else {
           //execute if database exists
            $this->conn = new mysqli($this->servername, $this->username, $this->password, $this->db);
            // Check connection
            if ($this->conn ->connect_error) {
                die("Connection failed: " . $this->conn ->connect_error); //stop execution if connection error
            } 
            $this::$count = 1; //initialize static count variable
        };
    }

    public function addTodo()
    {//method to add a todo to list
        echo "trial";
        if(isset($_REQUEST["addTodo"])) {
            //checks if value sent is empty or not
           
            if(!empty($_REQUEST["todo"]) ) {
                $todo = $this->test_input($_REQUEST["todo"]);//obtain value to be stored

                //stores input value in database
                $sql="INSERT INTO `todo` VALUES(NULL, '$todo', '')";
                $this->conn ->query($sql);
                header('location:side3.php');//reloads the page
            }
        }   
    }

    public function deleteTodo() { //method to delete a todo from list
       //obtain id of todo to delete
        $deleteId = $_REQUEST["deleteId"];
        
        //delete value from database
        $sql = "DELETE FROM `todo` WHERE `todo_id` = $deleteId";
        $this->conn ->query($sql);

        if ($this->conn ->connect_error) {
            die(); //stops execution of delete was not successful
        } 
        header('location:side3.php');//reloads the page
    }

    public function updateTodo() {// method to set status of an item on the list
        //obtain id of todo whose status is to be updated
        $updateId = $_REQUEST["updateId"];

        //update status of todo
        $sql = "UPDATE `todo` SET `status` = 'Done' WHERE `todo_id` = $updateId";
        $this->conn ->query($sql);

        if ($this->conn ->connect_error) {
            die(); //stops execution of update was not successful
        } 
        header('location:side3.php');//reloads the page
    }

    //validate todo passed to it
    private function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    function __destruct()
    {
        $this->conn ->close();//closes connection to database
    }
}

//set database connection variables
$servername = "localhost";
$username = "root";
$password = "";
$db = "mon";

//if connection has not being made to database creates `$test` object
//if connection to database exists, it prevents recreation of `Todo` class instance
if (!(Todo::$count > 0)) {
    $test = new Todo($servername,$username,$password,$db);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_REQUEST["addTodo"])) {
        //calls addTodo method of $test object
        
        $test->addTodo();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if (isset( $_REQUEST["updateId"])) {
      //calls updateTodo method of $test object
       $test->updateTodo();
    }

    if (isset( $_REQUEST["deleteId"])) {
        //calls deleteTodo method of $test object
       $test->deleteTodo();
    }
   
}
$test->i = 0;
//always fetches todos from database to be rendered
$todos = $test->conn->query("SELECT * FROM `todo` ORDER BY `todo_id` ASC");


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
<div  style="border:5px solid black;padding:2%;width:40%; margin-top:5%;margin-right:30%; margin-left:30%;">
<h1 style="margin-left: 30%;margin-right:30%;margin-bottom:8%">Todo App</h1>
<div >
			<div style="margin-left: 20%;margin-right:20%;margin-bottom:5%">
				<form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
					<input type="text"  name="todo" required/>
					<button  name="addTodo" type="submit">Add Task</button>
				</form>
			</div>
		</div>
		<br />
 <div>
 <div>
 <div  style="margin-left: 10%;margin-right:10%;">
					<span style="margin-right: 10%;">#</span>
					<span style="margin-right: 30%;">Task</span>
					<span style="margin-right: 10%;">Status</span>
					<span style="margin-right: 10%;">Action</span>
				</div>
 </div>
 <hr  style="width:80%">

                <?php
                //displays all todos gotten from database
					while($response = $todos->fetch_array()){
				?>
				<div style="margin-left: 10%;margin-right:5%; margin-bottom:2%;">
					<span style="margin-right: 10%;"><?php echo ++$test->i?></span>
					<span style="margin-right: 15%;"><?php echo $response['todo']?></span>
					<span style="margin-right: 5%;background-color:greenyellow"><?php echo $response['status']?></span>
					<span style="margin-left: 10%;border-left:1px solid black">
						
					<?php
								if($response['status'] != "Done"){
									echo '<button style="margin-right: 2%;margin-left:1%;background-color:yellow"><a href="'.htmlspecialchars($_SERVER["PHP_SELF"]).'?updateId='.$response['todo_id'].'">Done</a> </button>';
								}
					?>
							<button style="margin-right: 4%;margin-left:1%;background-color:red;text-color:white"> <a style="color:white" href="<?php echo htmlspecialchars($_SERVER["PHP_SELF"])?>?deleteId=<?php echo $response['todo_id']?>" >Delete</a></button>
					
					</span>
				</div>
                <hr style="width:80%;">
				<?php
					}
				?>
 
</div>
</body>
</html>