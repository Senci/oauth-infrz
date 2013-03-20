// initialize PDO
$db = new \PDO('sqlite:database_file.sqlite3');
$query = 'SELECT item FROM items WHERE color = :color';
$statement = $db->prepare($query);
// PDO has to fetch the objects as an Object with Class "Model\Car"
$statement->setFetchMode(\PDO::FETCH_CLASS, 'Model\Car');
// bind parameter ':color' to $oldColor
$statement->bindParam(':color', $oldColor);
$statement->execute();

foreach ($statement as $car) {
	$car->changeColor($newColor);
}
