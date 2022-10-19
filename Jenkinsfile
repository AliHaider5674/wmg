pipeline {
  agent {
    docker {
      image 'wmgathena/php:7.4.10-fpm-ci.1'
      args "--entrypoint=''"
    }
  }
  stages {
    stage("Build") {
      steps{
        sh "cp .env.testing .env"
        sh "composer install"
        sh "php ./vendor/bin/grumphp git:deinit"
      }
    }
    stage("Code Standard And Testing") {
        steps {
          sh "php -d memory_limit=5G ./vendor/bin/grumphp run"
        }
     }
    stage('deploy') {
      steps {
        build job: 'Deployment', parameters: [[$class: 'StringParameterValue', name: 'Environment', value: 'Stage'], [$class: 'StringParameterValue', name: 'Component', value: 'Fulfillment']], wait: false
      }
    }
  }
  post {
    failure {
      build job: 'Notification', parameters: [[$class: 'StringParameterValue', name: 'Subject', value: 'Pull Request CI Failed'], [$class: 'StringParameterValue', name: 'Body', value: "Pull Request Build Failed.</br>Project: ${env.JOB_NAME} <br>Build Number: ${env.BUILD_NUMBER} <br> URL de build: ${env.BUILD_URL}<br/>Author:${env.CHANGE_AUTHOR}<br/>Message:${env.CHANGE_TITLE}"]], wait: false
    }
  }
}
