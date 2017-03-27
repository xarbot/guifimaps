<?php
	/**
	* Dijkstra algorithm node class
	* 
	* @author Mallory Dessaintes - mdessaintes@gmail.com
	*/

	class DijkstraNode {
		const IMPOSSIBLE_DIST = -1;
		protected $_id;
		protected $_neighbours = array();
		protected $_distToSource = self::IMPOSSIBLE_DIST;
		protected static $_nodes = array();

		public function __construct($id,$links = array()) {
			$this->_id = $id;
			$this->_neighbours = $links;
			static::$_nodes[] = $this;
		}

		/**
		* Return all the nodes created
		*/
		public static function getNodes() {
			return static::$_nodes;
		}

		/**
		* Add a neighbour to the node, specifying the distance between it and the current node
		* 
		* @param bool $reciprocally Current node is also add as a neighbour of the given node
		*/
		public function addNeighbour(DijkstraNode $node,$dist = 1,$reciprocally = true) {
			if(!isset($this->_neighbours[$node->_id])) {
				$this->_neighbours[$node->_id] = array('node' => $node, 'dist' => $dist);
				if($reciprocally) {
					$node->_neighbours[$this->_id] = array('node' => $this, 'dist' => $dist);
				}
			}
			else {
				trigger_error('Neighbour already exists');
			}
		}

		/**
		* Return the neighbours of the node
		*/

		public function getNeighbours() {
			return array_values($this->_neighbours);
		}

		/**
		* Return the distance between the current node and another node (normally a neighbour node)
		*/

		public function getNeighbourDist(DijkstraNode $node) {
			if($node == $this) {
				return 0;
			}
			else {
				if(isset($this->_neighbours[$node->_id])) {
					return $this->_neighbours[$node->_id]['dist'];
				}
				else {
					// Node is not a neighbour
					return self::IMPOSSIBLE_DIST;
				}
			}
		}

		public function getId() {
			return $this->_id;
		}

		public function getDistToSource() {
			return $this->_distToSource;
		}

		public function setDistToSource($val) {
			$this->_distToSource = $val;
		}
	}

	/**
	* Main Dijkstra algorithm class
	* 
	* @author Mallory Dessaintes - mdessaintes@gmail.com
	*/

	class Dijkstra {
		protected static $_preds; // predecessors of the nodes
		protected static $_toVisit; // nodes to visit
		protected static $_visited; // nodes already visited
		protected static $_source; // Source node

		/**
		* Use Dijkstra algorithm to find the best route between source and all the nodes
		* 
		* @param array $nodes All the nodes, including the source
		* @param DijkstraNode $source The source node 
		* (we just use it to set the distance between it and "source" to 0, of course) and get a starting point
		* 
		* @return 
		*/

		public static function findRoute($nodes,DijkstraNode $source) {
			static::$_toVisit = $nodes;
			static::$_source = $source;
			static::_init();
			while(count(static::$_toVisit)) {
				static::_sortNodes();
				$node = static::$_toVisit[0];
				// Remove the current node from nodes to visit
				unset(static::$_toVisit[array_search($node,static::$_toVisit)]);
				// Test if have already found a path to this this which must be done ewcept if there iq no path 
				if($node->getDistToSource() != DijkstraNode::IMPOSSIBLE_DIST) {
					// Foreach neighbours of the current node
					foreach($node->getNeighbours() as $neighbourData) {
						$neighbour = $neighbourData['node']; // $neighbourData['dist'] = the distance between the node and this neighbour
						// Only if we doesn't already visited it (because if we had, the last past was necessarily shorter)
						if(in_array($neighbour,static::$_toVisit)) {
							// This is the current shortest distance between the neighbour in the loop and the source
							$neighbourDistSource = $neighbour->getDistToSource();
							// This is the current shortest distance between the current node and the 
							// source + the distance between the current node and the neighbour
							$neighbourDistCurrent = ($node->getDistToSource() + $node->getNeighbourDist($neighbour));
							// Checking if it is faster to go to the neighbour by the current node (or if the neighbour hasn't been reached yet (IMPOSSIBLE_DIST))
							if($neighbourDistSource == DijkstraNode::IMPOSSIBLE_DIST OR ($neighbourDistCurrent < $neighbourDistSource)) {
								// Set the new shortest distance between the neighbour and the source
								$neighbour->setDistToSource($neighbourDistCurrent);
								// Set the new predecessor of the neighbour in the path to the source
								static::$_preds[$neighbour->getId()] = $node->getId();
							}
						}
					}
				}
				// Add the current node to the visited nodes
				static::$_visited[] = $node;
			}
			$arrNodesDistsToSource = array();
			// Setting an array having key => nodeId, value => min distance to this node from the source
			foreach(static::$_visited as $node) {
				$arrNodesDistsToSource[$node->getId()] = $node->getDistToSource();
			}
			$ret = array(
				'paths' => static::$_preds, // key => node id, value => predecessor (node id) in the shortest path to the source
				'pathsCosts' => $arrNodesDistsToSource // Look at the last loop
			);
			return $ret;
		}
		private static function _init() {
			// Setting all predecessors of the nodes to null because default, they are unreachable
			foreach(static::$_toVisit as $node) {
				static::$_preds[$node->getId()] = null;
			}
			// Setting the distance between the source and itself to 0 to get 
			// a starting point in the algorithm (see sortNodes)
			static::$_source->setDistToSource(0);
		}
		/**
		* This sort the nodes to visit by their current shortest distances to the source
		* This method pay attention to IMPOSSIBLE_DIST which is set to -1
		*/
		private static function _sortNodes() {
			usort(static::$_toVisit,
				function ($a,$b) {
					$aDistToSource = $a->getDistToSource();
					$bDistToSource = $b->getDistToSource();
					if($aDistToSource == DijkstraNode::IMPOSSIBLE_DIST AND $bDistToSource == DijkstraNode::IMPOSSIBLE_DIST) {
						return 0;
					}
					elseif($aDistToSource == DijkstraNode::IMPOSSIBLE_DIST) {
						return 1;
					}
					elseif($bDistToSource == DijkstraNode::IMPOSSIBLE_DIST) {
						return -1;
					}
					elseif($aDistToSource > $bDistToSource) {
						return 1;
					}
					else if($aDistToSource < $bDistToSource) {
						return -1;
					}
					else {
						return 0;
					}
				}
			);
		}
	}
?>
