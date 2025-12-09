<?php
class Student {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function add($name, $email, $age, $faculty, $agree, $studyForm) {
        $sql = "INSERT INTO students (name, email, age, faculty, agree_rules, study_form) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$name, $email, $age, $faculty, $agree, $studyForm]);
    }

    public function getAll() {
        $sql = "SELECT * FROM students ORDER BY created_at DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function getByMinAge($minAge) {
        $sql = "SELECT * FROM students WHERE age >= ? ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$minAge]);
        return $stmt->fetchAll();
    }

    public function getByFaculty($faculty) {
        $sql = "SELECT * FROM students WHERE faculty = ? ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$faculty]);
        return $stmt->fetchAll();
    }

    public function getTotalCount() {
        $stmt = $this->pdo->query("SELECT COUNT(*) as total FROM students");
        $result = $stmt->fetch();
        return $result['total'];
    }

    public function getAverageAge() 
    {
        $stmt = $this->pdo->query("SELECT AVG(age) as avg_age FROM students");
        $result = $stmt->fetch();
        
        if ($result['avg_age'] === null) {
            return 0;
        }
        
        return round($result['avg_age'], 1);
    }

    public function getStatsByFaculty() {
        $sql = "SELECT faculty, COUNT(*) as count 
                FROM students 
                GROUP BY faculty 
                ORDER BY count DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function getStatsByStudyForm() {
        $sql = "SELECT study_form, COUNT(*) as count 
                FROM students 
                GROUP BY study_form 
                ORDER BY count DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function updateName($id, $name) {
        $sql = "UPDATE students SET name = ? WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$name, $id]);
    }

    public function delete($id) {
        $sql = "DELETE FROM students WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
    }
}
?>