<?php
class CITHotelsImportHandler extends SQLIImportAbstractHandler implements ISQLIImportHandler
{
    protected $rowIndex = 0;

    protected $rowCount;

    protected $currentGUID;

    public function __construct( SQLIImportHandlerOptions $options = null )
    {
        parent::__construct( $options );
        $this->remoteIDPrefix = $this->getHandlerIdentifier().'-';
        $this->currentRemoteIDPrefix = $this->remoteIDPrefix;
        $this->options = $options;
    }

    public function initialize()
    {
		$this->tree = array();
        $csvFile = $this->handlerConfArray['CsvFile'];
        $options = new SQLICSVOptions( array(
			'csv_path' => $csvFile,
            'delimiter' => ';',
			'enclosure'   => '"'
		) );
        $csvDoc = new SQLICSVDoc( $options );        
		$csvDoc->parse();        
        $this->dataSource = $csvDoc->rows;   
    }

    public function getProcessLength()
    {
        if( !isset( $this->rowCount ) )
        {
            $this->rowCount = count( $this->dataSource );
            $this->maxRowCount = $this->rowCount;
        }		
        return $this->rowCount;
    }

    public function getNextRow()
    {
        if( $this->rowIndex < $this->rowCount )
        {
            $row = $this->dataSource[$this->rowIndex];
            $this->rowIndex++;
        }
        else
        {
            $row = false; // We must return false if we already processed all rows
        }
        return $row;
    }

    public function process( $row )
    {                
        //id
        //mainLocale
        //categoryName
        //classificationsIt
        //classificationsDe
        //classificationsEn
        //name
        //stars
        //addressIt
        //addressDe
        //addressEn
        //urlPage
        //email
        //phone
        //phone2
        //fax
        //pictureUrl
        //shortDescriptionIt
        //shortDescriptionDe
        //shortDescriptionEn
        //pictureWidth
        //pictureHeight
        //latitude
        //longitude
        //status
        //lastModificationDate
        
        $this->currentGUID = $remote_id = $row->id;

        $contentOptions = new SQLIContentOptions( array(
            'class_identifier'      => 'accomodation',
            'remote_id'				=> $remote_id,
            'language'              => 'ita-IT'
        ) );
        $content = SQLIContent::create( $contentOptions );
		$content->fields->titolo = (string) $row->name;
        $content->fields->abstract = strip_tags( (string) $row->shortDescriptionIt );
        $content->fields->image = self::getImage( (string) $row->pictureUrl );        
		$content->fields->indirizzo = (string) $row->addressIt;
        $content->fields->gps = '1|#' . $row->latitude . '|#' . $row->longitude . '|#' . $row->addressIt;
        $content->fields->telefono = (string) $row->phone;
        $content->fields->telefono2 = (string) $row->phone2;		
        $content->fields->fax = (string) $row->fax;		
		$content->fields->stars = (string) $row->stars;		
		$content->fields->email = (string) $row->email;		
		$content->fields->url = (string) $row->urlPage;		
        
        $content->addTranslation( 'eng-GB' );		
		$content->fields['eng-GB']->titolo = (string) $row->name;
        $content->fields['eng-GB']->abstract = strip_tags( (string) $row->shortDescriptionEn );
        $content->fields['eng-GB']->image = self::getImage( (string) $row->pictureUrl );        
		$content->fields['eng-GB']->indirizzo = (string) $row->addressEn;
        $content->fields['eng-GB']->gps = '1|#' . $row->latitude . '|#' . $row->longitude . '|#' . $row->addressEn;
        $content->fields['eng-GB']->telefono = (string) $row->phone;
        $content->fields['eng-GB']->telefono2 = (string) $row->phone2;		
        $content->fields['eng-GB']->fax = (string) $row->fax;		
		$content->fields['eng-GB']->stars = (string) $row->stars;		
		$content->fields['eng-GB']->email = (string) $row->email;		
		$content->fields['eng-GB']->url = (string) $row->urlPage;			

        $parentNodeId = $this->handlerConfArray['DefaultParentNodeID'];
        $content->addLocation( SQLILocation::fromNodeID( $parentNodeId ) );
        $publisher = SQLIContentPublisher::getInstance();
        $publisher->publish( $content );
        unset( $content );
    }
    
    public static function getImage( $string )
    {
        return '/home/httpd/openpa.ezcommunity/html/extension/openpa_comuneintasca/data/images/' . $string;
    }


    public function cleanup()
    {
        return;
    }

    public function getHandlerName()
    {
        return 'Comune In Tasca Import Handler';
    }

    public function getHandlerIdentifier()
    {
        return 'citimporthandler';
    }

    public function getProgressionNotes()
    {
        return '';
    }
}