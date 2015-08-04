<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
	'pi_name' => 'Web Service Mundo Liderman',
	'pi_version' => '1.0',
	'pi_author' => 'Laboratoria',
	'pi_author_url' => 'http://laboratoria.la',
	'pi_description' => 'Plugin for retreiving data from Mundo Liderman Web Service',
	'pi_usage' => Webservice::usage()
);

class Webservice {

	public $return_data;
	private $custom_fields;
	private $salary_detail;

	public function __construct()
	{
		$this->EE =& get_instance();
		$this->CI =& get_instance();
		$this->EE->load->library('curl');
	}

	public static function usage()
	{
		ob_start();
?>	

Documentation:

Plugin for retreiving data from Mundo Liderman's Web Service

<?php
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

	public function boleta()
	{
		$member_id = $this->EE->session->userdata('member_id');
		$codigo_liderman_field_name = $this->getMemberFieldId("codigo-liderman");
		$token_field_name = $this->getMemberFieldId("token");
		$query = $this->EE->db->where('member_id', $member_id)
						 ->select("$codigo_liderman_field_name, $token_field_name")
				         ->get('exp_member_data');
		$codigoLiderman = $query->row($codigo_liderman_field_name);
		$token = $query->row($token_field_name);
		$periodo = trim(ee()->TMPL->fetch_param('periodo'));
		//return "Código Liderman : ". $codigoLiderman . ", Token : " . $token . ", Periodo : " . $periodo;
		$url = "http://190.187.13.164/WSIntranet/BoletaPago.svc/TraerCabeceraBoletaPago/$codigoLiderman/$periodo/$token";
		$data = $this->EE->curl->get($url);
		return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $data);
	}

	public function detalle_boleta_haberes()
	{
		return $this->detalle_boleta();
	}

	public function detalle_boleta_descuentos()
	{
		return $this->detalle_boleta();
	}

	public function detalle_boleta_aportaciones()
	{
		return $this->detalle_boleta();
	}

	public function tareo()
	{
		$member_id = $this->EE->session->userdata('member_id');
		$codigo_liderman_field_name = $this->getMemberFieldId("codigo-liderman");
		$token_field_name = $this->getMemberFieldId("token");
		$query = $this->EE->db->where('member_id', $member_id)
						 ->select("$codigo_liderman_field_name, $token_field_name")
				         ->get('exp_member_data');
		$codigoLiderman = $query->row($codigo_liderman_field_name);
		$token = $query->row($token_field_name);
		$mes = trim(ee()->TMPL->fetch_param('mes'));
		$currentMonth = date('n');
		if (!isset($mes)) {
			$mes = $currentMonth;
		}
		$meses_anticipacion = $currentMonth - ($mes + 1);
		$url = "http://190.187.13.164/WSIntranet/Tareo.svc/ListarTareo/$codigoLiderman/$meses_anticipacion/$token";
		$data = $this->EE->curl->get($url);
		$new_data = array();
		$i = 0;
		foreach ($data as $key => $value) {
			if ($value["Mes"] == ($mes+1)) {
				$new_data[$i] = $value;
				$i++;
			}
		}
		return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $new_data);
	}

	public function semaforotareo()
	{
		$member_id = $this->EE->session->userdata('member_id');
		$codigo_liderman_field_name = $this->getMemberFieldId("codigo-liderman");
		$token_field_name = $this->getMemberFieldId("token");
		$query = $this->EE->db->where('member_id', $member_id)
						 ->select("$codigo_liderman_field_name, $token_field_name")
				         ->get('exp_member_data');
		$codigoLiderman = $query->row($codigo_liderman_field_name);
		$token = $query->row($token_field_name);
		$mes = trim($this->EE->TMPL->fetch_param('mes'));
		$currentMonth = date('n');
		$mes = $currentMonth - $mes;
		$url = "http://190.187.13.164/WSIntranet/Tareo.svc/TraerSemaforoTareo/$codigoLiderman/$mes/$token";
		$data = $this->EE->curl->get($url);
		return $data;
	}

	private function getCustomMemberFields()
	{
		$member_fields = ee()->db->where('m_field_reg', 'y')
						 ->get("member_fields");
		$this->custom_fields = $member_fields->result_array();
	}

	private function getMemberFieldId($field_name)
	{
		$field_id = 0;
		if (!isset($this->custom_fields)) {
			$this->getCustomMemberFields();
		}
		foreach ($this->custom_fields as $key => $value) {
			if ($field_name == $value["m_field_name"]) {
				$field_id = $value["m_field_id"];
				break;
			}
		}
		return 'm_field_id_' . $field_id;
	}

	private function detalle_boleta() 
	{
		$member_id = $this->EE->session->userdata('member_id');
		$codigo_liderman_field_name = $this->getMemberFieldId("codigo-liderman");
		$token_field_name = $this->getMemberFieldId("token");
		$query = $this->EE->db->where('member_id', $member_id)
						 ->select("$codigo_liderman_field_name, $token_field_name")
				         ->get('exp_member_data');
		$codigoLiderman = $query->row($codigo_liderman_field_name);
		$token = $query->row($token_field_name);
		$periodo = trim(ee()->TMPL->fetch_param('periodo'));
		$url = "http://190.187.13.164/WSIntranet/BoletaPago.svc/TraerDetalleBoletaPago/$codigoLiderman/$periodo/$token";
		$data = $this->EE->curl->get($url);
		return $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $data);
	}

}