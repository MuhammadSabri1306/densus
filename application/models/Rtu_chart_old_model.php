<?php
class Rtu_chart_old_model extends CI_Model {

        public function __construct()
        {
                
                $this->load->database('opnimus');
        }

		public function get_lokasidata($sto)
		{
			$sql="SELECT * FROM juan5684_densus.rtu_map where sto_kode='$sto'";    
			$query = $this->db->query($sql);
			return $query->row_array();
		}
		
        public function get_kwhdata($rtu)
		{
			$sql="SELECT rtu_kode,ROUND(SUM(kwh_value)) as kwh_value,no_port,nama_port,tipe_port,satuan, timestamp 
			FROM kwh_counter 
			WHERE rtu_kode='$rtu'
			GROUP BY rtu_kode,DAY(timestamp) ORDER BY kwh_counter.timestamp ASC";    
			$query = $this->db->query($sql);
			return $query->row_array();
		}
        public function get_realkwh($rtu,$port)
		{
			$apiURL="https://opnimus.telkom.co.id/api/osasenewapi/getrtuport?token=xg7DT34vE7&flag=0&port=$port&rtuid=$rtu";
			$jsonrtukwh = file_get_contents($apiURL);
    		$datartukwh = json_decode($jsonrtukwh, TRUE);
			return $datartukwh[0]['VALUE'];
		}

		public function get_kwhtotal($rtu)
		{
			$sql="SELECT rtu_kode,ROUND(SUM(kwh_value)) as kwh_value
			FROM kwh_counter 
			WHERE rtu_kode='$rtu' AND YEAR(timestamp)=YEAR(CURRENT_DATE) AND MONTH(timestamp)=MONTH(CURRENT_DATE)
			GROUP BY rtu_kode,MONTH(timestamp) ORDER BY kwh_counter.timestamp ASC";    
			$query = $this->db->query($sql);
			return $query->row_array();
		}
		public function get_savingpercent($rtu)
		{
			//LAST Year YTD Perf
			$sql="SELECT rtu_kode,ROUND(SUM(kwh_value)) as kwh_value
			FROM kwh_counter 
			WHERE rtu_kode='RTU-BALA' AND YEAR(timestamp) = YEAR(CURRENT_DATE)-1
			GROUP BY rtu_kode,YEAR(timestamp) ORDER BY kwh_counter.timestamp ASC";    
			$query = $this->db->query($sql);
			$arrayly=$query->row_array();
			if ($arrayly['kwh_value']) $last_year=$arrayly['kwh_value'];else $last_year=0;
			


			//LAST Year YTD Perf
			$sql="SELECT rtu_kode,ROUND(SUM(kwh_value)) as kwh_value
			FROM kwh_counter 
			WHERE rtu_kode='$rtu' AND YEAR(timestamp) = YEAR(CURRENT_DATE)
			GROUP BY rtu_kode,YEAR(timestamp) ORDER BY kwh_counter.timestamp ASC";    
			$query = $this->db->query($sql);
			$arraycy=$query->row_array();
			$current_year=$arraycy['kwh_value'];

			//LAST Month YTD Perf
			$sql="SELECT rtu_kode,ROUND(SUM(kwh_value)) as kwh_value
			FROM kwh_counter 
			WHERE rtu_kode='$rtu' AND DATE(timestamp) BETWEEN DATE_FORMAT(NOW() ,'%Y-%m-01')- INTERVAL 1 MONTH AND NOW()- INTERVAL 1 MONTH
			GROUP BY rtu_kode,MONTH(timestamp) ORDER BY kwh_counter.timestamp ASC";    
			$query = $this->db->query($sql);
			$arraylm=$query->row_array();
			$last_month=$arraylm['kwh_value'];


			//LAST Month YTD Perf
			$sql="SELECT rtu_kode,ROUND(SUM(kwh_value)) as kwh_value
			FROM kwh_counter 
			WHERE rtu_kode='$rtu' AND DATE(timestamp) BETWEEN DATE_FORMAT(NOW() ,'%Y-%m-01') AND NOW()
			GROUP BY rtu_kode,MONTH(timestamp) ORDER BY kwh_counter.timestamp ASC";    
			$query = $this->db->query($sql);
			$arraycm=$query->row_array();
			$current_month=$arraycm['kwh_value'];

			//LAST DAY YTD Perf
			$sql="SELECT rtu_kode,timestamp,ROUND(SUM(kwh_value)) as kwh_value
			FROM kwh_counter 
			WHERE rtu_kode='$rtu' AND HOUR(timestamp) BETWEEN 0 AND HOUR(CURRENT_TIMESTAMP) AND DATE(timestamp)=DATE(CURRENT_DATE)-INTERVAL 1 DAY
			GROUP BY rtu_kode,DATE(timestamp) ORDER BY kwh_counter.timestamp ASC";    
			$query = $this->db->query($sql);
			$arrayld=$query->row_array();
			$last_day=$arrayld['kwh_value'];


			//LAST DAY YTD Perf
			$sql="SELECT rtu_kode,timestamp,ROUND(SUM(kwh_value)) as kwh_value
			FROM kwh_counter 
			WHERE rtu_kode='$rtu' AND HOUR(timestamp) BETWEEN 0 AND HOUR(CURRENT_TIMESTAMP) AND DATE(timestamp)=DATE(CURRENT_DATE)
			GROUP BY rtu_kode,DATE(timestamp) ORDER BY kwh_counter.timestamp ASC";    
			$query = $this->db->query($sql);
			$arraycd=$query->row_array();
			$current_day=$arraycd['kwh_value'];

			$savingpercent['savingyearly']=$last_year-$current_year;
			$savingpercent['savingyearly_percent']=$this->pct_change($current_year,$last_year);
			$savingpercent['savingmonthly']=$last_month-$current_month;
			$savingpercent['savingmonthly_percent']=$this->pct_change($current_month,$last_month);
			$savingpercent['savingdaily']=$last_day-$current_day;
			$savingpercent['savingdaily_percent']=$this->pct_change($current_day,$last_day);

			return $savingpercent;
		}
		public function get_bbmcost($rtu,$kva,$port,$bbmprice)
		{
            $sql="SELECT ROUND(0.21*$kva*SUM(TIMESTAMPDIFF(MINUTE,timestamp,timeended)/60.0)*$bbmprice) as bbm_cost
			FROM `juan5684_osasemobile`.`rtu_port_status`
		   WHERE rtu_port_status.rtu_kode='$rtu' AND port='$port' AND MONTH(CURRENT_DATE)=MONTH(timestamp) AND YEAR(CURRENT_DATE)=YEAR(timestamp)
		   GROUP BY MONTH(timestamp), YEAR(timestamp)
		   ORDER BY timestamp ASC";    
			$query = $this->db->query($sql);
            
			return $query->row_array();
		}
		public function get_kwhtoday($rtu)
		{
			$sql="SELECT rtu_kode,ROUND(SUM(kwh_value)) as kwh_value,timestamp
			FROM kwh_counter 
			WHERE rtu_kode='$rtu' AND DATE(timestamp)=DATE(CURRENT_DATE)
			GROUP BY rtu_kode,DATE(timestamp) ORDER BY kwh_counter.timestamp ASC";    
			$query = $this->db->query($sql);
			return $query->row_array();
		}
		public function get_totalAlarm($rtu)
		{
			$apiURL="https://opnimus.telkom.co.id/api/osasenewapi/getrtuport?token=xg7DT34vE7&flag=1&rtuid=$rtu";
			$jsonrtualarm = file_get_contents($apiURL);
    		$datartualarm = json_decode($jsonrtualarm, TRUE);
			if ($datartualarm['Status']) {
				return "0";
			}
			else return count($datartualarm);

		}
		public function get_tabledata($rtu,$biayalwbp,$biayawbp)
		{
		
			
			$index=0;
			$year=date("Y");
			$table_content;
			//ZONA INTEGRASI CHART
			// NEED FIX ASAP DYNAMIC SESUAI BULAN JALAN di TAHUN YG SAMA
			$table_content['chart']['Jan']=0;
			$table_content['chart']['Feb']=0;
			$table_content['chart']['Mar']=0;
			$table_content['chart']['Apr']=0;
			$table_content['chart']['May']=0;
			$table_content['chart']['Jun']=0;
			$table_content['chart']['Jul']=0;
			$table_content['chart']['Aug']=0;
			$table_content['chart']['Sep']=0;
			$table_content['chart']['Oct']=0;
			$table_content['chart']['Nov']=0;
			$table_content['chart']['Dec']=0;
			$table_content['chart']['Total_pln']=0;

			$biayalwbp=$biayalwbp;
			$biayawbp=$biayawbp;
			$sql="SELECT rtu_kode,ROUND(SUM(kwh_value)) as kwh_value,no_port,nama_port,tipe_port,satuan, timestamp 
			FROM kwh_counter 
			WHERE rtu_kode='$rtu'
			GROUP BY rtu_kode,MONTH(timestamp) ORDER BY kwh_counter.timestamp DESC";

			$query = $this->db->query($sql);
			foreach ($query->result() as $row)
			{
				$table_content['table'][$index]['periode']=date('F Y',strtotime($row->timestamp));
				$table_content['table'][$index]['total_kwh']=$row->kwh_value;
				$index++;
			}
			$index=0;

			$sql_lwbp="SELECT rtu_kode,ROUND(SUM(kwh_value)) as kwh_value
			FROM kwh_counter 
			WHERE rtu_kode='$rtu' AND HOUR(timestamp) NOT between 17 AND 22
			GROUP BY rtu_kode,MONTH(timestamp) ORDER BY kwh_counter.timestamp DESC";
			$query = $this->db->query($sql_lwbp);
			foreach ($query->result() as $row)
			{
				$table_content['table'][$index]['total_lwbp']=$row->kwh_value;
				$table_content['table'][$index]['biaya_lwbp']=$biayalwbp*($row->kwh_value);
				$index++;
			}
			$index=0;


			$sql_wbp="SELECT rtu_kode,ROUND(SUM(kwh_value)) as kwh_value,YEAR(timestamp) as tahun,DATE_FORMAT(timestamp, '%b') as bulan
			FROM kwh_counter 
			WHERE rtu_kode='$rtu' AND HOUR(timestamp) between 17 AND 22
			GROUP BY rtu_kode,MONTH(timestamp) ORDER BY kwh_counter.timestamp DESC";
			$query = $this->db->query($sql_wbp);
			foreach ($query->result() as $row)
			{
				$table_content['table'][$index]['total_wbp']=$row->kwh_value;
				$table_content['table'][$index]['biaya_wbp']=$biayawbp*($row->kwh_value);
				$table_content['table'][$index]['total_biaya']=$table_content['table'][$index]['biaya_wbp'] + $table_content['table'][$index]['biaya_lwbp'];
			
				//CHART OPERATION
				if ($row->tahun==$year) {
					$month=$row->bulan;
					$table_content['chart'][$month]=$table_content['table'][$index]['total_biaya'];
					$table_content['chart']['Total_pln']+=$table_content['chart'][$month];
				}
				
				$index++;
			}
			$index=0;
			// foreach ($query->result() as $row)
			// {
			// 	$percentChange =$this->pct_change($table_content[$index+1]['total_biaya'],$table_content[$index]['total_biaya']);
			// 	$table_content[$index]['cost_saving']=($percentChange*-1)."%";
			// 	return $table_content;
			// 	$index++;
			// }
			return $table_content;
		}
		public function get_deg_tabledata($rtu,$kva,$port,$bbmprice)
		{
			
			$index=0;
			$table_content;
			//NEED FIX ASAP KODINGAN SAMPAH
			$year=date("Y");
			$table_content['chart']['Jan']=0;
			$table_content['chart']['Feb']=0;
			$table_content['chart']['Mar']=0;
			$table_content['chart']['Apr']=0;
			$table_content['chart']['May']=0;
			$table_content['chart']['Jun']=0;
			$table_content['chart']['Jul']=0;
			$table_content['chart']['Aug']=0;
			$table_content['chart']['Sep']=0;
			$table_content['chart']['Oct']=0;
			$table_content['chart']['Nov']=0;
			$table_content['chart']['Dec']=0;
			$table_content['chart']['Total_genset']=0;

			$sql="SELECT rtu_kode,port,port_name,YEAR(timestamp) as tahun,MONTHNAME(timestamp) as bulan,ROUND(SUM(TIMESTAMPDIFF(MINUTE,timestamp,timeended)/60.0),2) as hours,SUM(TIMESTAMPDIFF(MINUTE,timestamp,timeended)) as minutes,COUNT(id) as jumlah,ROUND(0.21* $kva *SUM(TIMESTAMPDIFF(MINUTE,timestamp,timeended)/60.0)) as liter,ROUND(0.21* $kva *SUM(TIMESTAMPDIFF(MINUTE,timestamp,timeended)/60.0)*$bbmprice) as bbm_cost,DATE_FORMAT(timestamp, '%b') as bulan2
			FROM `juan5684_osasemobile`.`rtu_port_status`
			WHERE rtu_port_status.rtu_kode='$rtu' AND port='$port' 
			GROUP BY MONTH(timestamp), YEAR(timestamp)
			ORDER BY timestamp DESC";

			$query = $this->db->query($sql);
			foreach ($query->result() as $row)
			{
				$table_content['table'][$index]['periode']=$row->bulan." ".$row->tahun;
				$table_content['table'][$index]['rtu_port']=$row->rtu_kode." ".$row->port." ".$row->port_name;
				$table_content['table'][$index]['genset_on']=$row->jumlah;
				$table_content['table'][$index]['hours']=$row->hours;
				$table_content['table'][$index]['minutes']=$row->minutes;
				$table_content['table'][$index]['liter']=$row->liter;
				$table_content['table'][$index]['bbm_cost']=$row->bbm_cost;

				if ($row->tahun==$year) {
					$month=$row->bulan2;
					$table_content['chart'][$month]=$table_content['table'][$index]['bbm_cost'];
					$table_content['chart']['Total_genset']+=$table_content['chart'][$month];
				}
				$index++;
			}
			$index=0;

			return $table_content;
		}
		
		public function get_chartdata($rtu,$time)
		{
			$chart_content;
			$index=0;
			if ($time=="daily") {
				//LAST 3 days
				$sql_daily="SELECT kwh_value,timestamp
				FROM kwh_counter 
				WHERE rtu_kode='$rtu' AND DATE(timestamp) BETWEEN CURRENT_DATE-2 AND CURRENT_DATE
				 ORDER BY kwh_counter.timestamp DESC";
				$query = $this->db->query($sql_daily);
				$chart_content = $query->result();
			}
			return $chart_content;
		}
		
		
		public function pct_change($old, $new, int $precision = 2): float
		{
			if ($old == 0) {
				$old++;
				$new++;
			}
	
			$change = (($new - $old) / $old) * 100;
	
			return round($change, $precision);
		}
}