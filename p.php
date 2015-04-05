<?php
class RobotController extends Phalcon\Mvc\Controller{

    /*
     * Restituisce tutti i robot
     */
    public function getRobots(){

        $robots = Robots::query()
            ->order('name')
            ->execute();

        $data = array();
        foreach ($robots as $robot) {
            $data[] = array(
                'id' => $robot->id,
                'name' => $robot->name,
            );
        }

        return $data;
    }

    /*
     * Restituisce tutti i robot che contengono nel nome la stringa selezionata
     */
    public function searchByName($name){
        $robots = Robots::query()
            ->where("name LIKE :name:")
            ->order("name")
            ->bind(["name" => '%'.$name.'%'])
            ->execute();

        $data = array();
        foreach ($robots as $robot) {
            $data[] = array(
                'id' => $robot->id,
                'name' => $robot->name,
            );
        }
        return $data;
    }

    /*
     * Funzione restituisce il robot assiociato ad un determinato id
     */
    public function getRobot($id){
        $robot = Robots::findFirstById($id);

        $response = new \Phalcon\Http\Response();

        if($robot == false){
            $response -> setJsonContent(array(
                "status" => "NOT-FOUND"
            ));
        }else{
            $response -> setStatusCode(201, "COMPLETE");
            $response -> setJsonContent(array(
                "status" => "FOUND",
                "data" => $robot
            ));
        }

        return $response;
    }

    /*
     * Funzione che permette di aggiungere un robot
     */
    public function postRobot(){
        $request = new \Phalcon\Http\Request();

        $robot = $request -> getJsonRawBody();

        $newRobot = new Robots();        

        $newRobot->type = $robot-> type;
        $newRobot->name = $robot-> name;
        $newRobot->year = $robot-> year;

        $status = $newRobot->save();

        $response = new \Phalcon\Http\Response();

        //Check if the insertion was successful
        if ($status) {

            //Change the HTTP status
            $response ->setStatusCode(201, "Created");
            $data = array("id" => $newRobot->id);
            $response ->setJsonContent(array('status' => 'OK', 'data' => $data));

        } else {
            $controllerUtilities = new ControllerUtilities();
            $response = $controllerUtilities -> messageHandler($response, $newRobot);
        }
        return  $response;
    }

    /*
     * Funzione che permette di modificare il valore dei campi passati di un robot mediante l'id
     */
    public function patchRobot($id){

        $request = new \Phalcon\Http\Request();

        if($updateRobot = Robots::findFirstById($id)){
            $data =  json_decode($request->getRawBody(), true);
            $status = $updateRobot -> save($data);
            $row = $this -> db -> affectedRows();
        }else{
            $status = false;
        }

        $controllerUtilities = new ControllerUtilities();        
        $response = $controllerUtilities -> standardStatus($row, $status, $updateRobot);

        return $response;
    }

    /*
     * Funzione che permette di cancellare un robot dato l'id
     */
    public function deleteRobot($id){
        $row = 0;
        if($delateRobot = Robots::findFirstById($id)){
            $status = $delateRobot->delete();
            $row = $this -> db -> affectedRows();
        }else{
            $status = false;
        }

        $controllerUtilities = new ControllerUtilities();
        $response = $controllerUtilities -> standardStatus($row, $status, $delateRobot);

        return $response;
    }
}