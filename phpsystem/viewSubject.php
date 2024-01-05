<?php
require "formfunctions.php";
usercheck_login();

require_once('userconnection.php'); 


$errors = array();

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (isset($_POST['addGrade'])) {
        $errors = addGrade($_POST);
    }
}

if (isset($_GET['subject_id'])) {
    $subjectId = $_GET['subject_id'];
    
    $newconnection = new Connection();
    $connection = $newconnection->openConnection();
    $stmt = $connection->prepare("SELECT * FROM subjects WHERE subject_id = :subject_id");
    $stmt->bindParam(':subject_id', $subjectId);
    $stmt->execute();
    $subject = $stmt->fetch(PDO::FETCH_OBJ);

    $subjectLevelId = $subject->yearlevel_id;
    $subjectCourseId = $subject->course_id;
    $subjectId = $subject->subject_id;
}


$teacherId = $_SESSION['USER']->teacher_id;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link rel="stylesheet" href="viewSubject.css">
    <title>Document</title>
</head>
<body>
    <div class="sidebar"> 
        <div class="top">
        </div>
        <div class="user">
            <img src="<?= $_SESSION['USER']->teacher_profile?>" alt="">
            <div>
                <p class="name"><?= $_SESSION['USER']->teacher_name?></p>
                <p class="rank">Teacher</p>
            </div>
        </div>
        <ul>
            <li class="dashboard_btn">
                <a href="teacherhomepage.php">
                    <i class="bx bxs-grid-alt"></i>
                </a>
            </li>
            <li class="dashboard_btn">
                <a href="allstudents.php">
                    <i class="bx bxs-user"></i>
                </a>
            </li>
            <li class="logout_btn">
                <a href="logout.php" onclick="return confirm('Log out account?');">
                    <i class="bx bx-log-out"></i>
                </a>
            </li>
        </ul>
    </div>
    <div class="main-content">
        <div class="maincontainer">
            <?php
                require_once('userconnection.php'); 

                $departmentId = $_SESSION['USER']->department_id;

                $newconnection = new Connection();
                $connection = $newconnection->openConnection();

                $stmt = $connection->prepare("SELECT * FROM department WHERE department_id = :department_id");
                $stmt->bindParam(':department_id', $departmentId);
                $stmt->execute();
                
                $department = $stmt->fetch(PDO::FETCH_OBJ);
            ?>
            <div class="studentboxcontainer">
                <div class="studentcontainer">
                    <div class="studenttxt">Student List :</div>
                    <div class="student-box">
                        <?php
                        require_once("userconnection.php");

                        $departmentId = $_SESSION['USER']->department_id;

                        $newconnection = new Connection();
                        $connection = $newconnection->openConnection();

                        $sql = "SELECT * FROM students WHERE yearlevel_id = :yearlevel_id AND department_id = :department_id AND course_id = :course_id ORDER BY department_id";
                        $stmt = $connection->prepare($sql);
                        $stmt->bindParam(':department_id', $departmentId);
                        $stmt->bindParam(':yearlevel_id', $subjectLevelId);
                        $stmt->bindParam(':course_id', $subjectCourseId);
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

                            $department = $row['department_id'];

                            $stmtdepartment = $connection->prepare("SELECT * FROM department WHERE department_id = $department");
                            $stmtdepartment->execute();
                            $departments = $stmtdepartment->fetch(PDO::FETCH_OBJ);

                        ?>
                                <div class="student-card">
                                    <div class="studentprofileinfo">
                                        <div class="student-profile">
                                            <img src="<?= $departments->department_logo?>" alt="">
                                        </div>
                                        <div class="student-info">
                                            <div class="student-nametype">
                                                <div class="student-name">
                                                    <?= $row['student_name'] ?>
                                                </div>
                                                <div class="student-type">
                                                    <?= $row['department_name'] ?>
                                                </div>
                                            </div>
                                          </div>
                                    </div>
                                    <div class="courselevel">
                                        <?= $row['year_level'] ?> - <?= $row['course_name'] ?>
                                    </div>
                                    <div class="buttons">
                                        <button class="view-student-button" id="printButton" onclick="updateStatus(<?= $row['student_id'] ?>, <?= $subjectId ?>)"><img src="images/print.png" alt=""></button>
                                        
                                        <button class="view-student-button" onclick="openCreateModal(<?= $row['student_id'] ?>, <?= $subjectId ?>, <?= $teacherId ?>)"><img src="images/search.png" alt=""></button>
                                    </div>
                                </div>
                        <?php
                            }
                        } else {
                        ?>
                            <div class="nostudent">No student available.</div>
                        <?php
                        }
                        $newconnection->closeConnection();
                        ?>
                    </div>
                </div>
            </div>
            <?php
                require_once('userconnection.php');

                $newconnection = new Connection();
                $connection = $newconnection->openConnection();

                $stmt = $connection->prepare("SELECT * FROM department WHERE department_id = :department_id");
                $stmt->bindParam(':department_id', $departmentId);
                $stmt->execute();
                
                $department = $stmt->fetch(PDO::FETCH_OBJ);

            ?>
            <div id="myModal" class="modal">
                <div class="modal-content">
                    <span class="close" onclick="closeModal()">&times;</span>
                    <h2 id="modalTitle"><?=$subject->subject_name?> | <?=$subject->descriptive_title?></h2>
                    <label id="studentName"></label>
                    <form method="POST" enctype="multipart/form-data" id="semesterForm" class="createModal">
                        <div class="namedepartment">
                            <div class="name">
                                <label>Prelim :</label>
                                <input type="text" name="prelimScore" id="prelimScore" placeholder="Enter prelim score (100 maximum)">
                            </div>
                            <div class="department">
                                <label>Midterm :</label>
                                <input type="text" name="midtermScore" id="midtermScore" placeholder="Enter midterm score (100 maximum)">
                            </div>
                        </div>
                        <div class="namedepartment">
                            <div class="name">
                                <label>Semi-Final :</label>
                                <input type="text" name="semifinalScore" id="semifinalScore" placeholder="Enter semifinal score (100 maximum)">
                            </div>
                            <div class="department">
                                <label>Final :</label>
                                <input type="text" name="finalScore" id="finalScore" placeholder="Enter final score (100 maximum)">
                            </div>
                        </div>
                        <br><h1>FINAL GRADE :</h1>
                        <h2 id="finalGrade" name="finalGrade"></h2>
                        <p id="remarks"></p>
                        <input type="hidden" name="studentId" id="studentId">
                        <input type="hidden" name="subjectId" id="subjectId">
                        <input type="hidden" name="teacherId" id="teacherId">
                        <input type="hidden" name="semesterName" value="<?= $subject->semester?>">
                        <button class="submitbtn" name="addGrade" onclick="submitForm()">Done</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="viewSubject.js"></script>
</body>
</html>