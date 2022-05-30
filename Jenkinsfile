import groovy.json.JsonSlurper

/*
** Variables.
*/
properties([buildDiscarder(logRotator(numToKeepStr: '50'))])
def serie = '20.10'
def maintenanceBranch = "${serie}.x"
def qaBranch = "dev-${serie}.x"
env.REF_BRANCH = 'master'
env.PROJECT='centreon-dsm'
if (env.BRANCH_NAME.startsWith('release-')) {
  env.BUILD = 'RELEASE'
} else if ((env.BRANCH_NAME == env.REF_BRANCH) || (env.BRANCH_NAME == maintenanceBranch)) {
  env.BUILD = 'REFERENCE'
} else if ((env.BRANCH_NAME == 'develop') || (env.BRANCH_NAME == qaBranch)) {
  env.BUILD = 'QA'
} else {
  env.BUILD = 'CI'
}

def buildBranch = env.BRANCH_NAME
if (env.CHANGE_BRANCH) {
  buildBranch = env.CHANGE_BRANCH
}

/*
** Pipeline code.
*/
stage('Source') {
  node {
    sh 'setup_centreon_build.sh'
    dir('centreon-dsm') {
      checkout scm
    }
    sh "./centreon-build/jobs/dsm/${serie}/dsm-source.sh"
    source = readProperties file: 'source.properties'
    env.VERSION = "${source.VERSION}"
    env.RELEASE = "${source.RELEASE}"
    publishHTML([
      allowMissing: false,
      keepAll: true,
      reportDir: 'summary',
      reportFiles: 'index.html',
      reportName: 'Centreon DSM Build Artifacts',
      reportTitles: ''
    ])
  }
}

stage('RPM Packaging // Sonar analysis') {
  parallel 'centos7': {
    node {
      sh 'setup_centreon_build.sh'
      sh "./centreon-build/jobs/dsm/${serie}/dsm-package.sh centos7"
      archiveArtifacts artifacts: 'rpms-centos7.tar.gz'
      stash name: "rpms-centos7", includes: 'output/noarch/*.rpm'
      sh 'rm -rf output'
    }
  },
  /*'centos8': {
    node {
      sh 'setup_centreon_build.sh'
      sh "./centreon-build/jobs/dsm/${serie}/dsm-package.sh centos8"
    }
  }*/
  'sonar analysis': {
    node {
      sh 'setup_centreon_build.sh'
      withSonarQubeEnv('SonarQubeDev') {
        sh "./centreon-build/jobs/dsm/${serie}/dsm-analysis.sh"
      }
      timeout(time: 10, unit: 'MINUTES') {
        def qualityGate = waitForQualityGate()
        if (qualityGate.status != 'OK') {
          currentBuild.result = 'FAIL'
        }
      }
      if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
        error("Quality gate failure: ${qualityGate.status}.");
      }
    }
  }

  stage('Package') {
    parallel 'centos7': {
      node {
        checkoutCentreonBuild(buildBranch)
        sh "./centreon-build/jobs/dsm/${serie}/dsm-package.sh centos7"
        archiveArtifacts artifacts: 'rpms-centos7.tar.gz'
        stash name: "rpms-centos7", includes: 'output/noarch/*.rpm'
        sh 'rm -rf output'
      }
    },
    'RPM Packaging alma8': {
      node {
        checkoutCentreonBuild(buildBranch)
        sh "./centreon-build/jobs/dsm/${serie}/dsm-package.sh alma8"
        archiveArtifacts artifacts: 'rpms-alma8.tar.gz'
        stash name: "rpms-alma8", includes: 'output/noarch/*.rpm'
        sh 'rm -rf output'
      }
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Package stage failure.')
    }
  }

  if ((env.BUILD == 'RELEASE') || (env.BUILD == 'QA') || (env.BUILD == 'CI')) {
    stage('Delivery') {
      node {
        unstash 'rpms-centos7'
        unstash 'rpms-alma8'
        checkoutCentreonBuild(buildBranch)
        sh "./centreon-build/jobs/dsm/${serie}/dsm-delivery.sh"
      }
      if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
        error('Delivery stage failure.');
      }
    }
  }
}

if ((env.BUILD == 'RELEASE') || (env.BUILD == 'QA') || (env.BUILD == 'CI')) {
  stage('Delivery') {
    node {
      unstash 'rpms-centos7'
      sh 'setup_centreon_build.sh'
      sh "./centreon-build/jobs/dsm/${serie}/dsm-delivery.sh"
    }
  }
}
