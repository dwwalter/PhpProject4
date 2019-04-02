<?php

require_once ('functions01.php');

class Customer { 



    public $id;
    public $name;
    public $email;
    public $mobile;
	public $description;
	public $password; // text from form
	public $password_hashed; // hashed password
    
	public $path = '';
	
	private $noerrors = true;
    private $nameError = null;
    private $emailError = null;
    private $mobileError = null;
	private $passwordError = null;
    private $title = "Customer";
    private $tableName = "customers";

	
	// initialize $_FILES variables
	private $fileName = '';
	private $tmpName  = '';
	private $fileSize = '';
	private $fileType = '';
	private $content  = '';
	
	
	
	
    
    function create_record() { // display "create" form
        $this->generate_html_top (1);
        $this->generate_form_group("name", $this->nameError, $this->name, "autofocus");
        $this->generate_form_group("email", $this->emailError, $this->email);
        $this->generate_form_group("mobile", $this->mobileError, $this->mobile);
		$this->generate_form_group("password", $this->passwordError, $this->password,"","password");
        $this->generate_html_bottom (1);
    } // end function create_record()
    
    function read_record($id) { // display "read" form
        $this->select_db_record($id);
        $this->generate_html_top(2);
				echo"
				<div class='control-group col-md-6'>
					<div class='controls '>
					"; 
					if ($this->fileSize > 0) {
						$source = 'https://program4.000webhostapp.com/4.3/uploads/';
						echo '<img src=' . $source .  $this->fileName . ' width="250" height="250">   ';
					}
					else 
						echo 'No photo on file.';
					echo "
					</div>
				</div>
		
			"; 
        $this->generate_form_group("name", $this->nameError, $this->name, "disabled");
        $this->generate_form_group("email", $this->emailError, $this->email, "disabled");
        $this->generate_form_group("mobile", $this->mobileError, $this->mobile, "disabled");
        $this->generate_html_bottom(2);
    } // end function read_record()
    
    function update_record($id) { // display "update" form
        if($this->noerrors) $this->select_db_record($id);
        $this->generate_html_top(3, $id); 
						echo"
				<div class='control-group col-md-6'>
					<div class='controls '>
					"; 
					if ($this->fileSize > 0) {
						
						$source = 'https://program4.000webhostapp.com/4.3/uploads/';
						echo '<img src=' . $source .  $this->fileName . ' width="250" height="250">   ';
						
					}
					else 
						echo 'No photo on file.';
					echo"
					</div>
				</div>
		
			"; 
        $this->generate_form_group("name", $this->nameError, $this->name, "autofocus onfocus='this.select()'");
        $this->generate_form_group("email", $this->emailError, $this->email);
        $this->generate_form_group("mobile", $this->mobileError, $this->mobile);
		//display image control button
		echo"
			<div class='control-group <?php echo !empty($pictureError)?'error':'';?>
					<label class='control-label'>Picture</label>
					<div class='controls'>
						<input type='hidden' name='MAX_FILE_SIZE' value='16000000'>
						<input name='userfile' type='file' id='userfile'>
					</div>
					</div>
		";
        $this->generate_html_bottom(3);
    } // end function update_record()
    
    function delete_record($id) { // display "read" form
        $this->select_db_record($id);
        $this->generate_html_top(4, $id);
        $this->generate_form_group("name", $this->nameError, $this->name, "disabled");
        $this->generate_form_group("email", $this->emailError, $this->email, "disabled");
        $this->generate_form_group("mobile", $this->mobileError, $this->mobile, "disabled");
        $this->generate_html_bottom(4);
    } // end function delete_record()
	
	function login_form() { // display "login" form
        $this->generate_html_top (5);
        $this->generate_form_group("Email Address", $this->emailError, $this->email, "autofocus");
        $this->generate_form_group("Password", $this->passwordError, $this->password,"","password");
        $this->generate_html_bottom (5);
    } // end function login()
    
    /*
     * This method inserts one record into the table, 
     * and redirects user to List, IF user input is valid, 
     * OTHERWISE it redirects user back to Create form, with errors
     * - Input: user data from Create form
     * - Processing: INSERT (SQL)
     * - Output: None (This method does not generate HTML code,
     *   it only changes the content of the database)
     * - Precondition: Public variables set (name, email, mobile)
     *   and database connection variables are set in datase.php.
     *   Note that $id will NOT be set because the record 
     *   will be a new record so the SQL database will "auto-number"
     * - Postcondition: New record is added to the database table, 
     *   and user is redirected to the List screen (if no errors), 
     *   or Create form (if errors)
     */
    function insert_db_record () {
        if ($this->fieldsAllValid ()) { // validate user input
            // if valid data, insert record into table
            $pdo = Database::connect();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $sql = "INSERT INTO $this->tableName (name,email,mobile,password_hash) values(?, ?, ?, ?)";
            $q = $pdo->prepare($sql);
            $q->execute(array($this->name,$this->email,$this->mobile,MD5($this->password)));
            Database::disconnect();
            header("Location: $this->tableName.php"); // go back to "list"
        }
        else {
            // if not valid data, go back to "create" form, with errors
            // Note: error fields are set in fieldsAllValid ()method
            $this->create_record(); 
        }
    } // end function insert_db_record
    
    private function select_db_record($id) {
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "SELECT * FROM $this->tableName where id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id));
        $data = $q->fetch(PDO::FETCH_ASSOC);
        Database::disconnect();
        $this->name = $data['name'];
        $this->email = $data['email'];
        $this->mobile = $data['mobile'];
		$this->fileName = $data['fileName'];
        $this->fileSize = $data['fileSize'];
        $this->fileType = $data['fileType'];
		$this->content = $data['content'];
		$this->description = $data['description'];
		$this->path = $data['path'];
    } // function select_db_record()
    
    function update_db_record ($id) {
        // initialize $_FILES variables
		$this->fileName = $_FILES['userfile']['name'];
		$this->tmpName  = $_FILES['userfile']['tmp_name'];
		$this->fileSize = $_FILES['userfile']['size'];
		$this->fileType = $_FILES['userfile']['type'];
		$this->content = file_get_contents($this->tmpName);
		$this->description = $_POST['description']; 
		
		$this->path ='https://program4.000webhostapp.com/4.3/uploads/' . $this->fileName;
		
		$fileLocation = "uploads/";
        $fileFullPath = $fileLocation . $this->fileName;
		if($this->fileSize > 160000) {
					echo "File size caused an issue";
		}
		else{
			$this->id = $id;
			if (!file_exists($fileFullPath)) {		
				
				if ($this->fieldsAllValid()) {

					$this->noerrors = true;
					$pdo = Database::connect();
					$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
					$sql = "UPDATE $this->tableName  set name = ?, email = ?, mobile = ?, fileName = ?, fileSize = ?, fileType = ?, content = ?, description = ?, path = ? WHERE id = ?";
					$q = $pdo->prepare($sql);
					$q->execute(array($this->name, $this->email, $this->mobile, $this->fileName, $this->fileSize, $this->fileType, $this->content, $this->description, $this->path, $this->id));
					
					move_uploaded_file($this->tmpName, $fileFullPath);
					
					// list all files in database 
					// ORDER BY BINARY filename ASC (sorts case-sensitive, like Linux)
	 
					echo " 
						<div class='form-actions'>
						
							<a href='$this->tableName.php' class='btn btn-success'>Back to Customers</a> 
						</div> 
							<a href='https://program4.000webhostapp.com/4.3/uploads/' target='_blank'>Uploaded Pictures</a><br />";
	 
					echo '<br><br>All files in database...<br><br>';
					$sql = 'SELECT * FROM customers  ' 
						. 'ORDER BY BINARY fileName ASC;';
					$i = 0; 
					$source = 'https://program4.000webhostapp.com/4.3/uploads/';
					$path = '';
					$path2 = '';
					$pic = "No File or Description for this Customer";
					foreach ($pdo->query($sql) as $row) {
				
						$temp = $row['fileName'];
						if($temp != "") {
							$pic = ' <img src=' . $source .  $row['fileName'] . ' width="250" height="250">   ';
							$path = 'https://program4.000webhostapp.com/4.3/uploads/' . $row['fileName'];
						
							$path2 = " <a href=" . $source . $row['fileName'] . " target=  '_blank' > " . $path . "</a>";
						}	
				
						echo ' ... [' . $i++ . '] --- ' . $row['fileName'] . '       ' .$row['description'] .  
							  $pic .  $path2 .
							 
							 '<br>';
					}
					
					echo '<br><br>';

					// list all files in subdirectory
					echo 'All files in subdirectory...<br>';
					echo '<pre>';
					$arr = array_slice(scandir("$fileLocation"), 2);
					asort($arr);
					print_r($arr);
					echo '<pre>';
					echo '<br><br>';
				
					
					Database::disconnect();
				
				
				//	header("Location: $this->tableName.php");
				}
				else {
					$this->noerrors = false;
					$this->update_record($id);  // go back to "update" form
				}
			}
			else {
				echo "File <b><i>" . $this->fileName 
					. "</i></b> already exists. Please rename file.";
			
			}
		}
    }//end function update_db_record
    function delete_db_record($id) {
        $pdo = Database::connect();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql = "DELETE FROM $this->tableName WHERE id = ?";
        $q = $pdo->prepare($sql);
        $q->execute(array($id));
        Database::disconnect();
        header("Location: $this->tableName.php");
    } // end function delete_db_record()
    
    private function generate_html_top ($fun, $id=null) {
        switch ($fun) {
            case 1: // create
                $funWord = "Create"; $funNext = "insert_db_record"; 
                break;
            case 2: // read
                $funWord = "Read"; $funNext = "none"; 
                break;
            case 3: // update
                $funWord = "Update"; $funNext = "update_db_record&id=" . $id; 
                break;
            case 4: // delete
                $funWord = "Delete"; $funNext = "delete_db_record&id=" . $id; 
                break;
			case 5: // login
				 $funWord = "Login"; $funNext = "generate_form_group";
				 break;
            default: 
                echo "Error: Invalid function: generate_html_top()"; 
                exit();
                break;
        }
        echo "<!DOCTYPE html>
        <html>
            <head>
                <title>$funWord a $this->title</title>
                    ";
        echo "
                <meta charset='UTF-8'>
                <link href='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css' rel='stylesheet'>
                <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js'></script>
                <style>label {width: 5em;}</style>
                    "; 
        echo "
            </head>";
        echo "
            <body>
                <div class='container'>
                    <div class='span10 offset1'>
                        <p class='row'>
                            <h3>$funWord a $this->title</h3>
                        </p>
                        <form class='form-horizontal' action='$this->tableName.php?fun=$funNext' method='post' enctype='multipart/form-data'>                        
                    ";
    } // end function generate_html_top()
    
    private function generate_html_bottom ($fun) {
        switch ($fun) {
            case 1: // create
                $funButton = "<button type='submit' class='btn btn-success'>Create</button>"; 
                break;
            case 2: // read
                $funButton = "";
                break;
            case 3: // update
			echo "<h3>Description</h3>"; echo '<textarea rows="10" cols="35" name="description" ></textarea>';
                $funButton = "<button type='submit' class='btn btn-warning'>Update</button>";
                break;
            case 4: // delete
                $funButton = "<button type='submit' class='btn btn-danger'>Delete</button>"; 
                break;
			case 5: //login
				$funButton = "<button type='submit' class='btn btn-info'>Login</button>";
				break;
            default: 
                echo "Error: Invalid function: generate_html_bottom()"; 
                exit();
                break;
        }
        echo " 
                            <div class='form-actions'>
                                $funButton
                                <a class='btn btn-secondary' href='$this->tableName.php'>Back</a>
                            </div>
                        </form>
                    </div>

                </div> <!-- /container -->
            </body>
        </html>
                    ";
    } // end function generate_html_bottom()
    

		 private function generate_form_group ($label, $labelError, $val, $modifier="", $fieldType="text") {
        echo "<div class='form-group'";
        echo !empty($labelError) ? ' alert alert-danger ' : '';
        echo "'>";
        echo "<label class='control-label'>$label &nbsp;</label>";
        //echo "<div class='controls'>";
        echo "<input "
            . "name='$label' "
            . "type='$fieldType' "
            . "$modifier "
            . "placeholder='$label' "
            . "value='";
        echo !empty($val) ? $val : '';
        echo "'>";
        if (!empty($labelError)) {
            echo "<span class='help-inline'>";
            echo "&nbsp;&nbsp;" . $labelError;
            echo "</span>";
        }
        //echo "</div>"; // end div: class='controls'
        echo "</div>"; // end div: class='form-group'
    } // end function generate_form_group()
	
	
    private function fieldsAllValid () {
        $valid = true;
        if (empty($this->name)) {
            $this->nameError = 'Please enter Name';
            $valid = false;
        }
        if (empty($this->email)) {
            $this->emailError = 'Please enter Email Address';
            $valid = false;
        } 
        else if ( !filter_var($this->email,FILTER_VALIDATE_EMAIL) ) {
            $this->emailError = 'Please enter a valid email address: me@mydomain.com';
            $valid = false;
        }
        if (empty($this->mobile)) {
            $this->mobileError = 'Please enter Mobile phone number';
            $valid = false;	
        }
		
        return $valid;
    } // end function fieldsAllValid() 
    
    function list_records() {
		
        echo "<!DOCTYPE html>
        <html>
            <head>
                <title>$this->title" . "s" . "</title>
                    ";
        echo "
                <meta charset='UTF-8'>
                <link href='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/css/bootstrap.min.css' rel='stylesheet'>
                <script src='https://stackpath.bootstrapcdn.com/bootstrap/4.1.2/js/bootstrap.min.js'></script>
                    ";  
        echo "
            </head>
            <body>
                <a href='https://github.com/dwwalter/PhpProject4' target='_blank'>Github Repo</a><br />
                <a href='https://program4.000webhostapp.com/4.3/Project4_UML.pdf' target='_blank'>Diagram</a><br />
                <a href='https://program4.000webhostapp.com/4.3/Project4_Diagram.pdf' target='_blank'>FlowChart</a><br />
				<a href='https://program4.000webhostapp.com/4.3/uploads/' target='_blank'>Uploaded Pictures</a><br />

                <div class='container'>
                    <p class='row'>
                        <h3>$this->title" . "s" . "</h3>
                    </p>
                    <p>
                        <a href='$this->tableName.php?fun=display_create_form' class='btn btn-success'>Create</a>
						<a href='logout.php?' class='btn btn-danger'>Logout</a>
                    </p>
                    <div class='row'>
                        <table class='table table-striped table-bordered'>
                            <thead>
                                <tr>
									<th>Picture</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th>Action</th>
									
								</tr>
                            </thead>
                            <tbody>
                    ";
        $pdo = Database::connect();
        $sql = "SELECT * FROM $this->tableName ORDER BY id DESC";
		
		$source = 'https://program4.000webhostapp.com/4.3/uploads/';
						
        foreach ($pdo->query($sql) as $row) {
			$pic = "";
			$temp = $row['fileName'];
			if($temp != "") {
				$pic = ' <img src=' . $source .  $row['fileName'] . ' width="150" height="150">   ';
			}
			
            echo "<tr>";
			
			echo "<td>" . $pic . "</td>" ;

		
			echo "<td>". $row["name"] . "</td>";
            echo "<td>". $row["email"] . "</td>";
            echo "<td>". $row["mobile"] . "</td>";
		
            echo "<td width=250>";
            echo "<a class='btn btn-info' href='$this->tableName.php?fun=display_read_form&id=".$row["id"]."'>Read</a>";
            echo "&nbsp;";
            echo "<a class='btn btn-warning' href='$this->tableName.php?fun=display_update_form&id=".$row["id"]."'>Update</a>";
            echo "&nbsp;";
            echo "<a class='btn btn-danger' href='$this->tableName.php?fun=display_delete_form&id=".$row["id"]."'>Delete</a>";
            echo "</td>";
            echo "</tr>";
        }
        Database::disconnect();        
        echo "
                            </tbody>
                        </table>
                    </div>
                </div>

            </body>

        </html>
                    ";  
    } // end function list_records()
    
} // end class Customer