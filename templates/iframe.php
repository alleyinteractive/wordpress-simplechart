<?php if ( ! Simplechart::instance()->post_type->current_user_can() ) {
  die( esc_html__( 'Insufficient user capability', 'simplechart' ) );
} ?>

<!DOCTYPE html>
<html>
  <head lang="en">
    <meta charset="UTF-8">
    <title>Simplechart-React prototype</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/nvd3/1.8.1/nv.d3.min.css"/>
    <link rel="stylesheet" href="http://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
    <style>
        header {
          text-align: center;
        }
    </style>
  </head>
  <body>
    <header>
        <h1>Simplechart-React prototype</h1>
        <button onclick="mockParentMessage()">Mock parent window postMessage</button>
        <label for="toggle-colors">Apply custom palette
          <input type="checkbox" name="toggle-colors" id="toggle-colors" onclick="toggleDefaultColors()" checked />
        </label>
    </header>

    <div id='app'></div>

    <script src="<?php echo esc_url( Simplechart::instance()->get_plugin_url( 'js/app/bundle.js' ) ); ?>"></script>
    <script>
      customPalette = ['#ff0000','#fa001f','#f30033','#ec0045','#e50055','#df0062','#d70071','#cf007f','#c7008c','#c00097','#b600a5','#ad00ae','#a100bc','#9600c7','#8900d1','#7b00dc','#6c00e4','#5800ee','#3e00f7','#0000ff'];
      mockPostMessageData = {
        rawData: "Department,Head Count\nEmerging,3\nOperations,3\nPartners,5\nProject Management,5\nQA,1\nSales/Marketing,3\nUX/Design,4\nUX Devs,6\nWP Devs,22",
        chartOptions: {
          type: 'pieChart',
          color: customPalette
        },
        chartMetadata: {
          title: 'Our teams',
          caption: 'Alley by the numbers',
          credit: 'Namely'
        },
        chartData: [
          {
            label: 'Emerging',
            value: 3
          },
          {
            label: 'Operations',
            value: 3
          },
          {
            label: 'Partners',
            value: 5
          },
          {
            label: 'Project Management',
            value: 5
          },
          {
            label: 'QA',
            value: 1
          },
          {
            label: 'Sales/Marketing',
            value: 3
          },
          {
            label: '"UX/Design"',
            value: 3
          },
          {
            label: 'UX Devs',
            value: 3
          },
          {
            label: 'WP Devs',
            value: 22
          }
        ],
        messageType: 'bootstrapAppData'
      };

      function toggleDefaultColors() {
        if (document.getElementById('toggle-colors').checked) {
          mockPostMessageData.chartOptions.color = customPalette;
        } else if (mockPostMessageData.chartOptions.color) {
          delete mockPostMessageData.chartOptions.color;
        }
      }
      /**
       * Mock postMessage from parent window
       */
      function mockParentMessage() {
        window.postMessage(mockPostMessageData, '*');
      }
    </script>
  </body>
</html>
<?php die(); ?>
